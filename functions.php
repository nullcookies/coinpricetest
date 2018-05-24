<?php
// Cac phuong thuc telegram
include __DIR__.'/database/config.inc.php'; // Database Config
include __DIR__.'/database/Database.php'; // Class Database
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getData($id){
    $cached = apc_fetch($id);
    return $cached?$cached:'Flase';
}

function setData($id,$step){
    apc_store($id, $step, 60*60*12);
}

function removeData($id){
    apc_delete ($id);
}

// Lấy tên các plan hiện tại trong database
function getCurrentPlans() {
  $arrayPlans   =   array();
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayPlans = $db->query("SELECT :ten_plan FROM :table",['table'=>'plans', 'ten_plan' => 'ten_plan'])->fetch_all();
  $db->close();
  return $arrayPlans;
}

function getCurrentUser($telegramId) {

  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData = $db->query("SELECT `username` FROM :table WHERE `telegram_id` = ':telegram_id'",['table'=>'users','telegram_id'=> $telegramId])->fetch();

  return $arrayData['username'];
  $db->close();

}

function getUserEmail($userName) {
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData = $db->query("SELECT `email` FROM :table WHERE `username` LIKE ':username'",['table'=>'users','username'=> $userName])->fetch();

  return $arrayData['email'];
  $db->close();
}

function getFullName($userName) {
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery       =       $db->query("SELECT `ho_ten` FROM :table WHERE `username` LIKE ':username'",['table'=>'users','username'=> $userName])->fetch();
  return $arrayQuery['ho_ten'];
  $db->close();
}

function getTotalCoins($tenPlan) {
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData = $db->query("SELECT `tong_coin`, `ky_hieu_coin` FROM `plans` WHERE `ten_plan` = ':ten_plan'",[ 'ten_plan'=>$tenPlan])->fetch();
  return $arrayData;
  $db->close();
}

// Lấy toàn bộ thông tin của User
function getCurrentUserInfo($telegramId) {
  $result       =   '';
  $arrayResult  =   array();
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser  =   getCurrentUser($telegramId);
  $queryCheck   =   $db->findByCol('chitietplan','username', $currentUser);
  if(empty($queryCheck)) {
    $queryInfo    = $db->query("SELECT `ho_ten`, `email`, `facebook` FROM `users` WHERE `username` = ':username'",[ 'username'=>$currentUser])->fetch();
    $result   =   "Thông tin của bạn:\nUsername: ".$currentUser."\nHọ Tên: ".$queryInfo['ho_ten']."\nFacebook: ".$queryInfo['facebook']."\nEmail: ".$queryInfo['email'];
  } else {
    $queryInfo    =   $db->query("SELECT u.`ho_ten`, u.`email`, u.`facebook`,c.`ten_plan`, c.`so_vi` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` WHERE u.`username` = ':username' AND c.`so_dao_pos` NOT LIKE '0.00000%'",[ 'username'=>$currentUser])->fetch_all();
    foreach ($queryInfo as $key => $value) {
      foreach ($value as $k => $v) {
        $arrayResult['username']    =   $currentUser;
        $arrayResult['ho_ten']      =   $value['ho_ten'];
        $arrayResult['facebook']    =   $value['facebook'];
        $arrayResult['email']       =   $value['email'];
        if($k == 'ten_plan' || $k == 'so_vi') {
          $arrayResult['plan_tham_gia'][$key][$k]    =   $v;
        }
      }
    }
    /*echo '<pre>';
    print_r($arrayResult);
    echo '</pre>';*/

    $result   =   "Thông tin của bạn:\nUsername: ".$currentUser."\nHọ Tên: ".$arrayResult['ho_ten']."\nFacebook: ".$arrayResult['facebook']."\nEmail: ".$arrayResult['email'];
    foreach ($arrayResult['plan_tham_gia'] as $key => $value) {
      if(empty($value['so_vi'])) {
        $value['so_vi']   = 'Chưa đăng ký';
      }
      $result   .=    "\n-------------\nPlan ".strtoupper($value['ten_plan'])."\nSố Ví: ".$value['so_vi'];
    }
  }
  
  return $result;
  
  $db->close();
}

// Kiem Tra User và Password để login
function checkLogin($username, $password) {

    $result   =   false;
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);

    $arrayData = $db->query("SELECT * FROM :table WHERE `username` LIKE ':username' AND `password` = ':password'",['table'=>'users','username'=> $username,'password'=> $password ])->fetch();
    
    if(!empty($arrayData)) {
      $result   =   true;
    }
    return $result;
    $db->close();
}

// Kiểm tra user có trong plan hay không
function checkUserPlan($telegramId, $tenPlan) {
  $result     =   false;
  $db         =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser  =   getCurrentUser($telegramId);
  $arrayData  =   $db->query("SELECT * FROM :table WHERE `username` = ':username' AND `ten_plan` = ':ten_plan'",['table'=>'chitietplan','username'=> $currentUser, 'ten_plan' => $tenPlan])->fetch();
  if(!empty($arrayData)) {
    $result   =   true;
  }
  return $result;
  $db->close();
}

// Kiem tra so vi co ton tai trong hệ thống hay không
function checkUserWallet($requestWallet) {
  $result       =   false;
  $db           =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser  =   getCurrentUser($telegramId);
  $arrayData    =   $db->findByCol('chitietplan','so_vi',$requestWallet);
  if(!empty($arrayData)) {
    $result   =   true;
  }
  return $result;
  $db->close();
}

function checkStatusWallet() {
    $result   =   false;
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $queryData  =   $db->query("SELECT `active_so_vi` FROM :table GROUP BY `active_so_vi`",['table'=>'chitietplan'])->fetch();
    if($queryData['active_so_vi'] == true) {
        $result     =   true;
    }
    return $result;
    $db->close();
}

// Thêm telegram_id nếu user mới đăng nhập lần đầu
function insertTelegramId($userName, $telegramId) {
  $result         =   false;
  $db             =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery     =   $db->findByCol('users','telegram_id', $telegramId);
  
  if(!empty($arrayQuery)) {
    $db->update('users',['telegram_id'=> 0]," id = '".$arrayQuery['id']."'");
  }
  $arrayData  =   $db->query("SELECT * FROM :table WHERE `username` = ':username'",['table'=>'users','username'=> $userName ])->fetch();

  if(empty($arrayData['telegram_id'])) {
    $result = $db->update('users',['telegram_id'=> $telegramId]," username = '$userName'");
  }
  
  /*$arrayData  =   $db->query("SELECT * FROM :table WHERE `username` = ':username'",['table'=>'users','username'=> $userName ])->fetch();

  if(empty($arrayData['telegram_id'])) {
    $result = $db->update('users',['telegram_id'=> $telegramId]," username = '$userName'");
  }*/
  return $result;
   $db->close();
}

function insertUserInfo($telegramId, $infoText, $type, $tenPlan = null) {
  $result       =   '';
  $db           =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser  =   getCurrentUser($telegramId);
  if($type == 'email') {
    $result = $db->update('users',['email'=> $infoText]," username = '$currentUser'");
    if($result  ==   true) {
      $result   =   'Cập nhật Email thành công';
    }
  }

  if($type == 'ho_ten') {
    $result = $db->update('users',['ho_ten'=> $infoText]," username = '$currentUser'");
    if($result  ==   true) {
      $result   =   'Cập nhật họ tên thành công';
    }
  }

  if($type == 'facebook') {
    $result = $db->update('users',['facebook'=> $infoText]," username = '$currentUser'");
    if($result  ==   true) {
      $result   =   'Cập nhật Facebook thành công';
    }
  }

  if($type == 'so_vi') {
    $tenPlan  =   strtolower($tenPlan);
    $result = $db->update('chitietplan',['so_vi'=> $infoText]," username = '$currentUser' AND `ten_plan` = '$tenPlan'");
    if($result  ==   true) {
      $result   =   'Cập nhật Số Ví của Plan '.strtoupper($tenPlan).' thành công';
    }
  }

  return $result;
  $db->close();
}

// Kiểm tra thông tin Plan của User
function checkDetailPlan($telegramId, $request = null) {
  $db         =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $result_plans = $db->query("SELECT :tenplan_chitiet, :tai_dau_tu, :yeu_cau_khac FROM :table_chitiet WHERE (SELECT :username_users FROM :table_users WHERE :telegram_users = ':telegramId') = :username_chitiet AND `so_dao_pos` NOT LIKE '0.00000%'",['table_chitiet'=>'chitietplan', 'table_users'=>'users', 'username_users' => 'username', 'telegram_users' => 'telegram_id', 'telegramId' => $telegramId, 'username_chitiet' => 'username', 'tenplan_chitiet' => 'ten_plan', 'tai_dau_tu' => 'tai_dau_tu', 'yeu_cau_khac' => 'yeu_cau_khac'])->fetch_all();
  
  return $result_plans;
  $db->close();
}

// Kiểm tra chi tiết các plan
function answerPlanDetail($telegramId, $queryData) {
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayResult      =       array();
  $result           =       '';
  $getPlans         =       explode("_", $queryData);
  $currentPlan      =       $getPlans[1];
  $currentUser      =       getCurrentUser($telegramId);
  $queryCheck       =       $db->findByCol('chitietplan','username', $currentUser);
  if(empty($queryCheck)) {
    $result         =       'Bạn chưa tham gia Plan nào để theo dõi.';
  } else {
    $arrayResult  =   $db->query("SELECT * FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':current_plan'", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();

    $arrayPlanCoins   = $db->query("SELECT `tong_coin`, `ky_hieu_coin` FROM `plans` WHERE `ten_plan` = ':current_plan'", ['ten_plan' => 'ten_plan', 'current_plan' => $currentPlan])->fetch();

    $arrayUser          = $db->query("SELECT `ho_ten` FROM `users` WHERE `username` = ':username'", ['username' => $currentUser])->fetch();

    $arrayChiaLai        = $db->query("SELECT * FROM `chialai` WHERE `username` = ':username' AND `ten_plan` = ':current_plan' ORDER BY `ngay_chia_lai` DESC LIMIT 1", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();


      $result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTên Đăng Ký: ".$arrayUser['ho_ten']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']." ".$arrayPlanCoins['ky_hieu_coin']."\nCổ Phần: ".$arrayResult['co_phan']."%\nLãi mới nhất ngày ".$arrayChiaLai['ngay_chia_lai'].": ".$arrayChiaLai['lai_coin'] . ' '.$arrayPlanCoins['ky_hieu_coin'];
  }
  

  return $result;
  $db->close();
  
}

function updateRequestCoin($telegramId, $tenPlan, $updateText, $typeUpdate) {
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $userData         =       $db->query("SELECT `username` FROM :table WHERE `telegram_id` = ':telegram_id'",['table'=>'users','telegram_id'=> $telegramId ])->fetch();
  $currentUser      =       $userData['username'];
  if($typeUpdate == 'week') {
    $queryData = $db->update('chitietplan',['tai_dau_tu'=> $updateText]," `ten_plan` = '$tenPlan' AND `username` = '$currentUser'");
  } elseif($typeUpdate == 'month') {
    $queryData = $db->update('chitietplan',['yeu_cau_khac'=> $updateText]," `ten_plan` = '$tenPlan' AND `username` = '$currentUser'");
  }
  if($queryData  == true) {
    $result   =   "Cập nhật thành công";
  } else {
    $result   =   "Lỗi ! Vui lòng thử lại";
  }
  return $result;
  $db->close();
}

/*
**
** Các Function xử lý đăng ký
**
*/
// Kiểm tra user đã tồn tại hay chưa
function checkUserExisting($userName) {
  $result   =   false;
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  //$arrayData        =       $db->findByCol('users','username',$userName);
  $arrayData 		=	$db->query("SELECT * FROM :table WHERE `username` LIKE ':username'",['table'=>'users','username'=> $userName ])->fetch();
  if(!empty($arrayData)) {
    $result   =   true;
  }
  return $result;
  $db->close();
}

// Kiểm tra Email đã được đăng ký hay chưa
function checkEmailExisting($email) {
  $result   =   false;
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData        =       $db->findByCol('users','email',$email);
  if(!empty($arrayData)) {
    $result   =   true;
  }
  return $result;
  $db->close();
}

//Show tất cả các plans
function checkTelegramExisting($telegramId) {
  $result   =   '';
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData        =       $db->findByCol('users','telegram_id',$telegramId);
  if(!empty($arrayData)) {
    $result   =   true;
  }
  return $result;
  $db->close();
}

function insertNewUser($registerUser, $registerPassword, $registerFullname, $registerFacebook = null,$telegramId, $registerEmail = null) {
	$result   =   false;
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  	$result = $db->insert('users',['username'=>$registerUser,'password'=>$registerPassword, 'ho_ten'=>$registerFullname, 'facebook'=>$registerFacebook, 'telegram_id'=>$telegramId, 'email'=>$registerEmail, 'roles'=>'member']);

  	return $result;
  	$db->close();
}

function sendRegisterMail($registerUser = null, $registerPassword = null, $registerFullname = null, $registerFacebook = null, $registerEmail) {
    $mail = new PHPMailer(true);
    $result   =   false;
    $today = date("d/m/Y");
    try {
        $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  //gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'ta.team.rb@gmail.com';   //username
        $mail->Password = 'lyhxxnogvslxvfaz';   //password
        //$mail->Username = 'ngtanthanh90@gmail.com';   //username
        //$mail->Password = 'dthjhlqsogiadfmi';   //password
        // dthjhlqsogiadfmi
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;                    //smtp port

        $mail->setFrom('ta.team.rb@gmail.com', 'Registered From Telegram bot');
        $mail->addAddress($registerEmail, vn_to_str($registerFullname));

        /*$mail->addAttachment(__DIR__ . '/attachment1.png');
        $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

        $mail->isHTML(true);

        $mail->Subject = "Welcome to Team TA";
        $mail->Body    = "Xin chào $registerFullname !<br /> Bạn vừa đăng ký tài khoản từ Telegram Bot với thông tin sau:<br />Username: <b>$registerUser</b><br />Password: <b>$registerPassword</b><br />Họ Tên: <b>$registerFullname</b><br />Facebook: <b>$registerFacebook</b><br />Email: <b>$registerEmail</b><br />Vui lòng dùng username và password để đăng nhập vào Telegram Bot của chúng tôi<br />Xin cám ơn !";

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $result   =   true;
        }
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
    return $result;
}

function vn_to_str ($str){
 
  $unicode = array(
   
  'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
   
  'd'=>'đ',
   
  'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
   
  'i'=>'í|ì|ỉ|ĩ|ị',
   
  'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
   
  'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
   
  'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
   
  'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
   
  'D'=>'Đ',
   
  'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
   
  'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
   
  'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
   
  'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
   
  'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
   
  );
 
  foreach($unicode as $nonUnicode=>$uni){
   
  $str = preg_replace("/($uni)/i", $nonUnicode, $str);
   
  }
  $str = str_replace(' ','_',$str);
   
  return $str;
 
}

// Các Hàm Chuyển Coin
// 
function checkUserHaveEmail($telegramId) {
  $result           =       false;
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser      =       getCurrentUser($telegramId);
  $arrayCheck       =       $db->query("SELECT `email` FROM :table WHERE `username` = ':username'",['table'=>'users','username'=> $currentUser])->fetch();
  if(!empty($arrayCheck['email'])) {
    $result           =       true;
  }
  return $result;
  $db->close();
}

// Kiểm tra username đã tồn tại trong hệ thống hay chưa
function checkUserExchange($userName) {
  $result           =       false;
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayCheck       =       $db->query("SELECT `username` FROM :table WHERE `username` LIKE ':username'",['table'=>'users','username'=> $userName])->fetch();
  if(!empty($arrayCheck['username'])) {
    $result           =       true;
  }
  return $result;
  $db->close();
}

// Liệt kê danh sách các plan user đã tham gia

function checkCoinUser($telegramId) {
  //SELECT `c`.`ten_plan`, `c`.`so_dao_pos`, `p`.`ky_hieu_coin` FROM `chitietplan` AS `c` INNER JOIN `plans` AS `p` ON `c`.`ten_plan` = `p`.`ten_plan` WHERE `username` = (SELECT `username` FROM `users` WHERE `telegram_id` = '338838500') AND `so_dao_pos` NOT LIKE '0.00000%'
  $result           =       '';
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery       =       $db->query("SELECT `c`.`ten_plan`, `c`.`so_dao_pos`, `p`.`ky_hieu_coin` FROM `chitietplan` AS `c` INNER JOIN `plans` AS `p` ON `c`.`ten_plan` = `p`.`ten_plan` WHERE `username` = (SELECT `username` FROM `users` WHERE `telegram_id` = ':telegram_id') AND `so_dao_pos` NOT LIKE '0.00000%'",['telegram_id'=> $telegramId])->fetch_all();
  if(!empty($arrayQuery)) {
    $result        .=       "Các plan bạn đang tham gia: \n";
  }
  foreach($arrayQuery as $key => $value) {
    $result        .=       "Plan ".strtoupper($value['ten_plan'])."\n Số coin: ".$value['so_dao_pos']." ".$value['ky_hieu_coin']."\n----------------------------------\n";
  }
  $result           .=      "Ghi tên plan bạn muốn chuyển coin:";
  return $result;
  $db->close();
}

function checkUserChoosePlan($telegramId, $tenPlan) {
  $result               =       false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery           =       $db->query("SELECT `c`.`ten_plan`, `c`.`so_dao_pos`, `p`.`ky_hieu_coin` FROM `chitietplan` AS `c` INNER JOIN `plans` AS `p` ON `c`.`ten_plan` = `p`.`ten_plan` WHERE `username` = (SELECT `username` FROM `users` WHERE `telegram_id` = ':telegram_id') AND `so_dao_pos` NOT LIKE '0.00000%'",['telegram_id'=> $telegramId])->fetch_all();
  foreach($arrayQuery as $key => $value) {
    if(in_array(strtolower($tenPlan), $value)) {
      $result           =       true;
      break;
    } else {
      continue;
    }
  }
  return $result;
  $db->close();
}

function checkEnoughCoinTransfer($telegramId, $tenPlan, $soCoinChuyen = null) {
  $result               =       false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser          =       getCurrentUser($telegramId);
  $arrayQuery           =       $db->query("SELECT `so_dao_pos` FROM :table WHERE `username` LIKE ':username' AND `ten_plan` = ':ten_plan'",['table'=>'chitietplan','username'=> $currentUser, 'ten_plan'=> strtolower($tenPlan)])->fetch();
  $userCoin                   =       (double)$arrayQuery['so_dao_pos'];
  $soCoinChuyen               =       (double)$soCoinChuyen;
  if($soCoinChuyen < $userCoin) {
    $result               =       true;
  }
  return $result;
  $db->close();
}

function sendConfirmExchange($telegramId, $emailUserSend, $userTo, $coinTransfer, $coinName) {
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $token                =       'qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM0123456789';
  $token                =       str_shuffle($token);
  $token                =       substr($token, 0, 10);
  $mail                 =       new PHPMailer(true);
  $result               =       false;
  $today                =       date("Y-m-d H:i:s");
  $currentUser          =       getCurrentUser($telegramId);
  $userFullName         =       getFullName($currentUser);
  $db->insert('transactions',['user_chuyen'=> $currentUser, 'user_nhan'=> $userTo, 'so_coin_chuyen' => $coinTransfer, 'ten_coin' => $coinName,'ngay_chuyen'=> $today, 'isConfirm' => 0, 'token' => $token]);
  try {
      $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
          )
      );
      $mail->isSMTP();
      $mail->Host       =   'smtp.gmail.com';  //gmail SMTP server
      $mail->SMTPAuth   =   true;
      $mail->Username   =   'ta.team.rb@gmail.com';   //username
      $mail->Password   =   'lyhxxnogvslxvfaz';   //password
      //$mail->Username = 'ngtanthanh90@gmail.com';   //username
      //$mail->Password = 'dthjhlqsogiadfmi';   //password
      // dthjhlqsogiadfmi
      $mail->SMTPSecure =   'ssl';
      $mail->Port       =   465;                    //smtp port
      $mail->CharSet    =   'UTF-8';
      $mail->setFrom('ta.team.rb@gmail.com', 'TeamTA Telegram Bot');
      $mail->addAddress($emailUserSend, $userFullName);

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

      $mail->isHTML(true);

      $mail->Subject = "Code xác nhận chuyển coin";
      $mail->Body    = "Xin chào ".$userFullName."<br />Bạn đang thực hiện chuyển coin cho user: <b>".$userTo."</b><br />Số coin chuyển: <b>".$coinTransfer." ".$coinName."</b><br />Vui lòng nhập code dưới đây vào bot telegram để xác nhận việc chuyển coin: <b>".$token."</b><br />Lưu ý: giao dich sẽ bị hủy nếu bạn nhập sai code<br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

      if (!$mail->send()) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      } else {
          $result   =   true;
          $mail->ClearAllRecipients();
      }
  } catch (Exception $e) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    return $result;
  $db->close();
}

// Kiểm tra code xác nhận
function checkConfirmCode($userChuyen, $userNhan, $confirmCode) {
  $result   =   false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery           =       $db->query("SELECT `id`, `status` FROM :table WHERE `user_chuyen` LIKE ':user_chuyen' AND `user_nhan` LIKE ':user_nhan' AND `token` = ':token' AND `isConfirm` = 0",['table'=>'transactions','user_chuyen'=> $userChuyen, 'user_nhan'=> $userNhan, 'user_nhan'=> $userNhan, 'token' => $confirmCode])->fetch();
  if(!empty($arrayQuery['id']) && $arrayQuery['id'] != 'failed') {
    $result   =   true;
  } else {
    $db->update('transactions',['status'=>'failed']," id = '".$arrayQuery['id']."'");
  }
  return $result;
  $db->close();
}

//
function updateStatusTransactions($userChuyen, $userNhan, $confirmCode, $adminFee, $tenCoin) {
  $result   =   false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $today                =       date("d/m/Y");
  $arrayQuery           =       $db->query("SELECT `id` FROM :table WHERE `user_chuyen` LIKE ':user_chuyen' AND `user_nhan` LIKE ':user_nhan' AND `token` = ':token' AND `isConfirm` = 0",['table'=>'transactions','user_chuyen'=> $userChuyen, 'user_nhan'=> $userNhan, 'user_nhan'=> $userNhan, 'token' => $confirmCode])->fetch();
  if(!empty($arrayQuery['id'])) {
    $result = $db->update('transactions',['isConfirm'=> 1,'token'=>'', 'status'=>'success']," id = '".$arrayQuery['id']."'");
    $adminFee     =   (double)$adminFee;
    updateAdminFee($today, $adminFee, $tenCoin, $arrayQuery['id']);
  }
  return $result;
  $db->close();
}

function transferUserCoin($userChuyen, $userNhan, $soCoinChuyen, $tenCoin, $coinFee = null) {
  $result                         =       false;
  $db                             =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQueryUserChuyen           =       $db->query("SELECT `so_dao_pos` FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':ten_plan' ",['username'=> $userChuyen, 'ten_plan'=> strtolower($tenCoin)])->fetch();
  $arrayQueryUserNhan             =       $db->query("SELECT `so_dao_pos` FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':ten_plan' ",['username'=> $userNhan, 'ten_plan'=> strtolower($tenCoin)])->fetch();
  $soCoinUserChuyen               =       $arrayQueryUserChuyen['so_dao_pos'];
  $soCoinUserNhan                 =       $arrayQueryUserChuyen['so_dao_pos'];

  $soCoinUserChuyen               =       $soCoinUserChuyen - $soCoinChuyen;
  $soCoinUserNhan                 =       $soCoinUserNhan + $soCoinChuyen;

  $result                         =       $db->update('chitietplan',['so_dao_pos'=> $soCoinUserChuyen]," username LIKE '".$userChuyen."' AND ten_plan = '".$tenCoin."'");

  $result                         =       $db->update('chitietplan',['so_dao_pos'=> $soCoinUserNhan]," username LIKE '".$userNhan."' AND ten_plan = '".$tenCoin."'");

  if($result  == true) {
      sendEmailToUserChuyen($userChuyen, $userNhan, $soCoinChuyen, $soCoinUserChuyen, $tenCoin, $coinFee);
      sendEmailToUserNhan($userChuyen, $userNhan, $soCoinChuyen, $soCoinUserNhan, $tenCoin, $coinFee);
  }

  return $result;
  $db->close();
}

function sendEmailToUserChuyen($userChuyen, $userNhan, $soCoinChuyen, $soCoin, $tenCoin, $coinFee = null) {
  $db                               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $mail                             =       new PHPMailer(true);
  $result                           =       false;
  $today                            =       date("d/m/Y");
  $userChuyenEmail                  =       getUserEmail($userChuyen);
  $userChuyenFullName               =       getFullName($userChuyen);
  $userNhanFullName                 =       getFullName($userNhan);

  $soCoinUserChuyenLucDau           =       $soCoin + $soCoinChuyen;
  $tenCoin                          =       strtoupper($tenCoin);
  try {
      $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
          )
      );
      $mail->isSMTP();
      $mail->Host       =   'smtp.gmail.com';  //gmail SMTP server
      $mail->SMTPAuth   =   true;
      $mail->Username   =   'ta.team.rb@gmail.com';   //username
      $mail->Password   =   'lyhxxnogvslxvfaz';   //password
      //$mail->Username = 'ngtanthanh90@gmail.com';   //username
      //$mail->Password = 'dthjhlqsogiadfmi';   //password
      // dthjhlqsogiadfmi
      $mail->SMTPSecure =   'ssl';
      $mail->Port       =   465;                    //smtp port
      $mail->CharSet    =   'UTF-8';
      $mail->setFrom('ta.team.rb@gmail.com', 'TeamTA Telegram Bot');
      $mail->addAddress($userChuyenEmail, $userChuyenFullName);

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

      $mail->isHTML(true);

      $mail->Subject = "Giao dịch chuyển Coin thành công";
      $mail->Body    = "Xin chào ".$userChuyenFullName."<br />Bạn vừa thực hiện thành công giao dịch chuyển coin vào ngày ".$today." với thông tin như sau: <br />User nhận: <b>".$userNhan." (".$userNhanFullName.")</b><br />Số Coin ban đầu: <b>".$soCoinUserChuyenLucDau." ".$tenCoin."</b><br />Số Coin chuyển: <b>".$soCoinChuyen." ".$tenCoin."</b> (Đã bao gồm fee ".$coinFee."%)<br />Số Coin còn lại: <b>".$soCoin." ".$tenCoin."</b><br />Vui lòng kiểm tra bot telegram để xem lại thông tin<br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

      if (!$mail->send()) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      } else {
          $result   =   true;
      }
  } catch (Exception $e) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    return $result;
  $db->close();
}

function sendEmailToUserNhan($userChuyen, $userNhan, $soCoinChuyen, $soCoin, $tenCoin, $coinFee = null) {
  $db                                 =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $mail                               =       new PHPMailer(true);
  $result                             =       false;
  $today                              =       date("d/m/Y");
  $userNhanEmail                      =       getUserEmail($userNhan);
  $userNhanFullName                   =       getFullName($userNhan);
  $userChuyenFullName                 =       getFullName($userChuyen);

  $soCoinUserNhanLucDau               =       $soCoin - $soCoinChuyen;
  $tenCoin                            =       strtoupper($tenCoin);
  try {
      $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
          )
      );
      $mail->isSMTP();
      $mail->Host       =   'smtp.gmail.com';  //gmail SMTP server
      $mail->SMTPAuth   =   true;
      $mail->Username   =   'ta.team.rb@gmail.com';   //username
      $mail->Password   =   'lyhxxnogvslxvfaz';   //password
      //$mail->Username = 'ngtanthanh90@gmail.com';   //username
      //$mail->Password = 'dthjhlqsogiadfmi';   //password
      // dthjhlqsogiadfmi
      $mail->SMTPSecure =   'ssl';
      $mail->Port       =   465;                    //smtp port
      $mail->CharSet    =   'UTF-8';
      $mail->setFrom('ta.team.rb@gmail.com', 'TeamTA Telegram Bot');
      $mail->addAddress($userNhanEmail, $userNhanFullName);

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

      $mail->isHTML(true);

      $mail->Subject = "Giao dịch chuyển Coin thành công";
      $mail->Body    = "Xin chào ".$userNhanFullName."<br />Bạn vừa thực hiện thành công giao dịch chuyển coin vào ngày ".$today." với thông tin như sau: <br />User chuyển: <b>".$userChuyen." (".$userChuyenFullName.")</b><br />Số Coin ban đầu: <b>".$soCoinUserNhanLucDau." ".$tenCoin."</b><br />Số Coin nhận: <b>".$soCoinChuyen." ".$tenCoin."</b> (Đã bao gồm fee ".$coinFee."%)<br />Số Coin sau khi nhận: <b>".$soCoin." ".$tenCoin."</b><br />Vui lòng kiểm tra bot telegram để xem lại thông tin<br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

      if (!$mail->send()) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      } else {
          $result   =   true;
      }
  } catch (Exception $e) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    return $result;
  $db->close();
}

function sendEmailToAdmin($userName, $soCoin, $tenCoin, $idGiaoDich = null) {
  $db                                 =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $mail                               =       new PHPMailer(true);
  $result                             =       false;
  $today                              =       date("d/m/Y");
  $userEmail                          =       getUserEmail($userName);
  $userFullName                       =       getFullName($userNhan);

  $tenCoin                            =       strtoupper($tenCoin);
  try {
      $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
          )
      );
      $mail->isSMTP();
      $mail->Host       =   'smtp.gmail.com';  //gmail SMTP server
      $mail->SMTPAuth   =   true;
      $mail->Username   =   'ta.team.rb@gmail.com';   //username
      $mail->Password   =   'lyhxxnogvslxvfaz';   //password
      //$mail->Username = 'ngtanthanh90@gmail.com';   //username
      //$mail->Password = 'dthjhlqsogiadfmi';   //password
      // dthjhlqsogiadfmi
      $mail->SMTPSecure =   'ssl';
      $mail->Port       =   465;                    //smtp port
      $mail->CharSet    =   'UTF-8';
      $mail->setFrom('ta.team.rb@gmail.com', 'TeamTA Telegram Bot');
      $mail->addAddress($userEmail, $userFullName);

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

      $mail->isHTML(true);

      $mail->Subject = "Nhận Coin từ Giao Dịch - ID: ".$idGiaoDich;
      $mail->Body    = "Xin chào ".$userFullName."<br />Bạn vừa nhận được <b>".$soCoin." ".$tenCoin."</b> từ giao dịch có ID:".$idGiaoDich."<br />Vui lòng kiểm tra trong telegram bot<br />Xin cám ơn !";

      if (!$mail->send()) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      } else {
          $result   =   true;
      }
  } catch (Exception $e) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    return $result;
  $db->close();
}

function updateAdminFee($ngayNhan, $soCoinNhan, $tenCoin, $idGiaoDich) {
  $result         =       false;
  $coinAdminGet   =       0;
  $db             =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery     =       $db->query("SELECT `username`, `roles` FROM `users` WHERE `roles` = ':admin' OR `roles` = ':dev' ",['admin'=> 'admin', 'dev'=> 'dev'])->fetch_all();
  foreach($arrayQuery as $key => $value) {
    if($value['roles'] == 'admin') {
        $coinAdminGet     =   (double)$soCoinNhan * 0.2;
    } else if($value['roles'] == 'dev') {
        $coinAdminGet     =   (double)$soCoinNhan * 0.3;
    }
    $arrayCoin              =       $db->query("SELECT `so_dao_pos` FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':ten_plan' ",['username'=> $value['username'], 'ten_plan'=> strtolower($tenCoin)])->fetch();
    $userCoin               =       (double)$arrayCoin['so_dao_pos'];

    $result                 =       $db->insert('admin_fee',['username'=>$value['username'],'ngay_nhan'=> $ngayNhan, 'so_coin_nhan'=>$coinAdminGet, 'ten_coin'=>$tenCoin, 'id_giao_dich' => $idGiaoDich]);

    $userCoin       =    (double)$userCoin + (double)$coinAdminGet;

    $result         = $db->update('chitietplan',['so_dao_pos'=> $userCoin]," username LIKE '".$value['username']."' AND ten_plan = '".strtolower($tenCoin)."'");
    //sendEmailToAdmin($value['username'], $coinAdminGet, $tenCoin, $idGiaoDich);
  }
  return $result;
  $db->close();
}