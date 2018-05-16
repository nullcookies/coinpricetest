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

  $result 	=		"Thông tin của bạn:\nUsername: ".$currentUser."\nHọ Tên: ".$arrayResult['ho_ten']."\nFacebook: ".$arrayResult['facebook']."\nEmail: ".$arrayResult['email'];
  foreach ($arrayResult['plan_tham_gia'] as $key => $value) {
  	if(empty($value['so_vi'])) {
  		$value['so_vi'] 	=	'Chưa đăng ký';
  	}
  	$result 	.=		"\n-------------\nPlan ".strtoupper($value['ten_plan'])."\nSố Ví: ".$value['so_vi'];
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

// Thêm telegram_id nếu user mới đăng nhập lần đầu
function insertTelegramId($userName, $telegramId) {
  $result     =   false;
  $db         =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData  =   $db->query("SELECT * FROM :table WHERE `username` = ':username'",['table'=>'users','username'=> $userName ])->fetch();

  if(empty($arrayData['telegram_id'])) {
    $result = $db->update('users',['telegram_id'=> $telegramId]," username = '$userName'");
  }
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

  $arrayResult  =   $db->query("SELECT * FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':current_plan'", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();

  $arrayPlanCoins   = $db->query("SELECT `tong_coin`, `ky_hieu_coin` FROM `plans` WHERE `ten_plan` = ':current_plan'", ['ten_plan' => 'ten_plan', 'current_plan' => $currentPlan])->fetch();

  $arrayUser          = $db->query("SELECT `ho_ten` FROM `users` WHERE `username` = ':username'", ['username' => $currentUser])->fetch();

  $arrayChiaLai        = $db->query("SELECT * FROM `chialai` WHERE `username` = ':username' AND `ten_plan` = ':current_plan' ORDER BY `ngay_chia_lai` DESC LIMIT 1", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();


  	$result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTên Đăng Ký: ".$arrayUser['ho_ten']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']." ".$arrayPlanCoins['ky_hieu_coin']."\nCổ Phần: ".$arrayResult['co_phan']."%\nLãi mới nhất ngày ".$arrayChiaLai['ngay_chia_lai'].": ".$arrayChiaLai['lai_coin'] . ' '.$arrayPlanCoins['ky_hieu_coin'];

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
  $arrayData        =       $db->findByCol('users','username',$userName);
  if(!empty($arrayData)) {
    $result   =   true;
  }
  return $result;
  $db->close();
}

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

//
function showAllPlans() {
  $result   =   '';
  $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData        =       $db->query("SELECT `ten_plan` FROM :table WHERE `ten_plan` != 'bullcoin'",['table'=>'plans'])->fetch_all();

  foreach ($arrayData as $key => $value) {
    $result   .=  "- ".strtoupper($value['ten_plan'])."\n";
  }
  return $result;
  $db->close();
}

function sendRegisterMail($registerUser = null, $registerPassword = null, $registerFullname = null, $registerFacebook = null, $registerEmail = null, $registerPlan = null, $registerWallet = null, $registerOther = null) {
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
        $mail->addAddress("ta.team.rb@gmail.com");

        /*$mail->addAttachment(__DIR__ . '/attachment1.png');
        $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

        $mail->isHTML(true);

        $mail->Subject = "User Register Email - date: $today";
        $mail->Body    = "Xin chào !<br /> Có user đăng ký từ bot Telegram với thông tin sau:<br />Username: <b>$registerUser</b><br />Password: <b>$registerPassword</b><br />Họ Tên: <b>$registerFullname</b><br />Facebook: <b>$registerFacebook</b><br />Email: <b>$registerEmail</b><br />Plan đăng ký: <b>$registerPlan</b><br />Số ví: <b>$registerWallet</b><br />Yêu cầu khác: <b>$registerOther</b><br />";

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