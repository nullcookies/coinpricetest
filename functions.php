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

function clearCache() {
  apc_clear_cache();
  apc_clear_cache('user');
  apc_clear_cache('opcode');
}

function checkNgayChiaLai(){
	$result 	=	false;
	date_default_timezone_set('Asia/Ho_Chi_Minh');
	$date = new DateTime();
	if($date->format('D') === 'Fri')  {
		$result 	=	true;
	} 
	return $result;
}

function checkSendMailTimes($telegramId) {
  $currentUser        =       getCurrentUser($telegramId);
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayPlans = $db->query("SELECT `sendmail_times` FROM :table WHERE `username` = ':username'",['table'=>'users', 'username' => $currentUser])->fetch();
  $db->close();
  return $arrayPlans['sendmail_times'];  
}

// Lấy tên các plan hiện tại trong database
function getCurrentPlans() {
  $arrayPlans   =   array();
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayPlans = $db->query("SELECT :ten_plan FROM :table WHERE `active` = 1",['table'=>'plans', 'ten_plan' => 'ten_plan'])->fetch_all();
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

function getKyHieuCoin($tenPlan) {
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData = $db->query("SELECT `ky_hieu_coin` FROM `plans` WHERE `ten_plan` = ':ten_plan'",[ 'ten_plan'=>$tenPlan])->fetch();
  return $arrayData['ky_hieu_coin'];
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

function checkUserRoles($userName) {
  $db         =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayData  =   $db->query("SELECT `roles` FROM :table WHERE `username` LIKE ':username'",['table'=>'users','username'=> $userName])->fetch();
  return $arrayData['roles'];
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
  
  if(!empty($arrayQuery['id'])) {
    $db->update('users',['telegram_id'=> 0]," id = '".$arrayQuery['id']."'");
  }
  $arrayData  =   $db->query("SELECT * FROM :table WHERE `username` LIKE ':username'",['table'=>'users','username'=> $userName ])->fetch();

  //if(empty($arrayData['telegram_id'])) {
    $result = $db->update('users',['telegram_id'=> $telegramId]," username = '$userName'");
  //}
  
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
    $emailChange    =   strtolower($infoText);
    $queryCheck     =       $db->findByCol('users','email', $emailChange);
    if(!empty($queryCheck)) {
      $result   =   'Email này đã được đăng ký, vui lòng nhấn nút sửa Email khác !';
    } else {
      $result = $db->update('users',['email'=> $emailChange]," username = '$currentUser'");
      if($result  ==   true) {
        $result   =   'Cập nhật Email thành công';
      }
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

//Kiểm Tra Trạng Thái Rút Ngày/Tuần
function checkStatusDailyRequest($telegramId, $tenPlan, $requestType = null) {
	$result 			=		'';
	$db         		=   	new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$currentUser      	=       getCurrentUser($telegramId);
	if($requestType == 'daily') {
		$arrayQuery =   $db->query("SELECT `yeu_cau_ngay` FROM `chitietplan` WHERE `username` LIKE ':username' AND `ten_plan` = ':ten_plan'", ['ten_plan' => $tenPlan, 'username'=>$currentUser])->fetch();
		$result 		=		'Trạng thái: '.ucfirst($arrayQuery['yeu_cau_ngay']).' rút';
	} else if($requestType == 'weekly') {
		$arrayQuery =   $db->query("SELECT `tai_dau_tu` FROM `chitietplan` WHERE `username` LIKE ':username' AND `ten_plan` = ':ten_plan'", ['ten_plan' => $tenPlan, 'username'=>$currentUser])->fetch();
		$result 		=		'Trạng thái: '.ucfirst($arrayQuery['tai_dau_tu']).' rút';
	}
	return $result;
	$db->close();
}

// Kiểm Tra Plan có cho rút ngày hay không
function checkDailyWithdraw($tenPlan) {
	$db         =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayQuery =   $db->query("SELECT `ghi_chu` FROM `plans` WHERE `ten_plan` = ':ten_plan'", ['ten_plan' => $tenPlan])->fetch();
	return $arrayQuery['ghi_chu'];
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

    //$arrayChiaLai        = $db->query("SELECT * FROM `chialai` WHERE `username` = ':username' AND `ten_plan` = ':current_plan' ORDER BY `ngay_chia_lai` DESC LIMIT 1", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();
    //SELECT * FROM `chialai` WHERE `username` = 'tanthanh' AND `ten_plan` = 'apr-pos' ORDER BY STR_TO_DATE(ngay_chia_lai, '%d/%m/%Y') DESC LIMIT 1
    $arrayChiaLai        = $db->query("SELECT * FROM `chialai` WHERE `username` = ':username' AND `ten_plan` = ':current_plan' ORDER BY STR_TO_DATE(ngay_chia_lai, '%d/%m/%Y') DESC LIMIT 1", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();

      $result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTên Đăng Ký: ".$arrayUser['ho_ten']."\nBalance: ".$arrayResult['so_dao_pos']." ".$arrayPlanCoins['ky_hieu_coin']."\nCổ Phần: ".$arrayResult['co_phan']."%\nLãi mới nhất ngày ".$arrayChiaLai['ngay_chia_lai'].": ".$arrayChiaLai['lai_coin'] . ' '.$arrayPlanCoins['ky_hieu_coin'];
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
  } elseif($typeUpdate == 'daily') {
    $queryData = $db->update('chitietplan',['yeu_cau_ngay'=> $updateText]," `ten_plan` = '$tenPlan' AND `username` = '$currentUser'");
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
  $soDaoPos 		=		'';
  foreach($arrayQuery as $key => $value) {
  	$soDaoPos 		=		(double)$value['so_dao_pos'];
    $result        .=       "Plan ".strtoupper($value['ten_plan'])."\n Số coin: ".$soDaoPos." ".$value['ky_hieu_coin']."\n----------------------------------\n";
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

function createRandomToken() {
	$token                =       'qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM0123456789';
  	$token                =       str_shuffle($token);
  	$token                =       substr($token, 0, 10);
  	return $token;
}

function updateFailed($tokenCode) {
	$result   =   false;
  	$db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  	$arrayQuery           =       $db->query("SELECT `id` FROM :table WHERE `txtid` = ':token_code'",['table'=>'transactions', 'token_code' => $tokenCode])->fetch();

  if(!empty($arrayQuery['id'])) {
  	$result 	=	$db->update('transactions',['status'=> 'failed']," txtid = '".$tokenCode."'");
  } 
  return $result;
  $db->close();
}

function sendConfirmExchange($telegramId, $emailUserSend, $userTo, $coinTransfer, $coinName, $tokenCode) {
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  
  $mail                 =       new PHPMailer(true);
  $result               =       false;
  $today                =       date("Y-m-d H:i:s");
  $currentUser          =       getCurrentUser($telegramId);
  $userFullName         =       getFullName($currentUser);
  $db->insert('transactions',['user_chuyen'=> $currentUser, 'user_nhan'=> $userTo, 'so_coin_chuyen' => $coinTransfer, 'ten_coin' => $coinName,'ngay_chuyen'=> $today, 'isConfirm' => 0, 'token' => $tokenCode, 'txtid' => $tokenCode]);
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
      $mail->Body    = "Xin chào ".$userFullName."<br />Bạn đang thực hiện chuyển coin cho user: <b>".$userTo."</b><br />Số coin chuyển: <b>".$coinTransfer." ".$coinName."</b><br />Vui lòng nhập code dưới đây vào bot telegram để xác nhận việc chuyển coin: <b>".$tokenCode."</b><br />Lưu ý: <b>Code có hiệu lực trong vòng 5 phút, giao dich sẽ bị hủy nếu bạn nhập sai code</b><br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

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
function checkConfirmCode($userChuyen, $userNhan, $tokenCode, $confirmCode) {
  $result   =   false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery           =       $db->query("SELECT `id`, `status`, `token` FROM :table WHERE `user_chuyen` LIKE ':user_chuyen' AND `user_nhan` LIKE ':user_nhan' AND `txtid` = ':token_code' AND `isConfirm` = 0",['table'=>'transactions','user_chuyen'=> $userChuyen, 'user_nhan'=> $userNhan, 'user_nhan'=> $userNhan, 'token_code' => $confirmCode])->fetch();

  if(!empty($arrayQuery['id']) && $arrayQuery['status'] != 'failed') {
  	if(trim($arrayQuery['token']) == trim($confirmCode)) {
  		$result   =   true;
  	} else {
	  	$db->update('transactions',['status'=> 'failed']," txtid = '".$tokenCode."'");
	  }
  } else {
	  	$db->update('transactions',['status'=> 'failed']," txtid = '".$tokenCode."'");
  } 
  return $result;
  $db->close();
}

//
function updateStatusTransactions($userChuyen, $userNhan, $confirmCode, $adminFee, $tenCoin) {
  $result   =   '';
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery           =       $db->query("SELECT `id` FROM :table WHERE `user_chuyen` LIKE ':user_chuyen' AND `user_nhan` LIKE ':user_nhan' AND `token` = ':token' AND `isConfirm` = 0",['table'=>'transactions','user_chuyen'=> $userChuyen, 'user_nhan'=> $userNhan, 'user_nhan'=> $userNhan, 'token' => $confirmCode])->fetch();
  if(!empty($arrayQuery['id'])) {
    $result = $db->update('transactions',['isConfirm'=> 1,'token'=>'', 'status'=>'success']," id = '".$arrayQuery['id']."'");
    $result 	=	$arrayQuery['id'];  
  }
  return $result;
  $db->close();
}

function transferUserCoin($userChuyen, $userNhan, $soCoinChuyen, $tenCoin, $coinFee = null) {
  $result                         =       false;
  $db                             =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  if(checkUserRoles($userNhan) == 'admin' || checkUserRoles($userNhan) == 'dev') {
  	$coinFee 					  =		  0;
  }
  $arrayQueryUserChuyen           =       $db->query("SELECT `so_dao_pos` FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':ten_plan' ",['username'=> $userChuyen, 'ten_plan'=> strtolower($tenCoin)])->fetch();
  $arrayQueryUserNhan             =       $db->query("SELECT `so_dao_pos` FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':ten_plan' ",['username'=> $userNhan, 'ten_plan'=> strtolower($tenCoin)])->fetch();
  if(!empty($arrayQueryUserNhan['so_dao_pos'])) {
  	$coinUserChuyen                 =       (double)$arrayQueryUserChuyen['so_dao_pos'];
  	$coinUserNhan                   =       (double)$arrayQueryUserNhan['so_dao_pos'];

  	$soCoinChuyen 				  =		  (double)$soCoinChuyen;

  	$soCoinUserChuyen               =       $coinUserChuyen - $soCoinChuyen;
  	$coinWithFee                    =       $soCoinChuyen - ($soCoinChuyen * $coinFee);
  	$soCoinUserNhan                 =       $coinUserNhan + $coinWithFee;

  	$result                         =       $db->update('chitietplan',['so_dao_pos'=> $soCoinUserChuyen]," username LIKE '".$userChuyen."' AND ten_plan = '".$tenCoin."'");

  	$result                         =       $db->update('chitietplan',['so_dao_pos'=> $soCoinUserNhan]," username LIKE '".$userNhan."' AND ten_plan = '".$tenCoin."'");

	  if($result  == true) {
	      sendEmailToUserChuyen($userChuyen, $userNhan, $soCoinChuyen, $soCoinUserChuyen,$coinUserChuyen, $tenCoin, $coinFee);
	      sendEmailToUserNhan($userChuyen, $userNhan, $soCoinChuyen, $soCoinUserNhan,$coinUserNhan, $tenCoin, $coinFee);
	  }
  } else {
  	$coinUserChuyen                 =       (double)$arrayQueryUserChuyen['so_dao_pos'];
  	$coinUserNhan                   =       0;

  	$soCoinChuyen 				  	=		 (double)$soCoinChuyen;

  	$soCoinUserChuyen               =       $coinUserChuyen - $soCoinChuyen;
  	$coinWithFee                    =       $soCoinChuyen - ($soCoinChuyen * $coinFee);
  	$soCoinUserNhan                 =       $coinUserNhan + $coinWithFee;

  	$result                         =       $db->update('chitietplan',['so_dao_pos'=> $soCoinUserChuyen]," username LIKE '".$userChuyen."' AND ten_plan = '".$tenCoin."'");

  	$result                         =       $db->update('chitietplan',['so_dao_pos'=> $soCoinUserNhan]," username LIKE '".$userNhan."' AND ten_plan = '".$tenCoin."'");
  	$result 						=		$db->insert('chitietplan',['username'=>$userNhan,'ten_plan'=> strtolower($tenCoin), 'so_dao_pos'=> $soCoinUserNhan, 'so_dau_tu'=>'0.00000000', 'co_phan'=> '0.00', 'tai_dau_tu'=>'không', 'active_so_vi' => 1]);

	  if($result  == true) {
	      sendEmailToUserChuyen($userChuyen, $userNhan, $soCoinChuyen, $soCoinUserChuyen,$coinUserChuyen, $tenCoin, $coinFee);
	      sendEmailToUserNhan($userChuyen, $userNhan, $soCoinChuyen, $soCoinUserNhan,$coinUserNhan, $tenCoin, $coinFee);
	  }
  }
  

  /*echo 'userChuyen: '. $userChuyen . '<br />';
  echo 'userNhan: '. $userNhan . '<br />';
  echo 'soCoinChuyen: '. $soCoinChuyen . '<br />';
  echo 'tenCoin: '. $tenCoin . '<br />';
  echo 'coinFee: '. $coinFee . '<br />';
  echo 'coinUserChuyen: '. $coinUserChuyen . '- type: '. gettype($coinUserChuyen). '<br />';
  echo 'coinUserNhan: '. $coinUserNhan . '- type: '. gettype($coinUserNhan) . '<br />';
  echo 'số Coin User Chuyển Sau Khi Chuyển: '. $soCoinUserChuyen . '- type: '. gettype($soCoinUserChuyen). '<br />';
  echo 'số Coin User Nhận Sau Khi Nhận: '. $soCoinUserNhan . '- type: '. gettype($soCoinUserNhan) . '<br />';*/

  return $result;
  $db->close();
}

function sendEmailToUserChuyen($userChuyen, $userNhan, $soCoinChuyen, $soCoinLucSau,$soCoinLucDau, $tenCoin, $coinFee = null) {
  $db                               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $mail                             =       new PHPMailer(true);
  $result                           =       false;
  $today                            =       date("d/m/Y");
  $userChuyenEmail                  =       getUserEmail($userChuyen);
  $userChuyenFullName               =       getFullName($userChuyen);
  $userNhanFullName                 =       getFullName($userNhan);

  //$soCoinUserChuyenLucDau           =       $soCoin + $soCoinChuyen;
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
      $mail->Body    = "Xin chào ".$userChuyenFullName."<br />Bạn vừa thực hiện thành công giao dịch chuyển coin vào ngày ".$today." với thông tin như sau: <br />User nhận: <b>".$userNhan." (".$userNhanFullName.")</b><br />Số Coin ban đầu: <b>".$soCoinLucDau." ".$tenCoin."</b><br />Số Coin chuyển: <b>".$soCoinChuyen." ".$tenCoin."</b><br />Số Coin còn lại: <b>".$soCoinLucSau." ".$tenCoin."</b><br />Vui lòng kiểm tra bot telegram để xem lại thông tin<br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

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

function sendEmailToUserNhan($userChuyen, $userNhan, $soCoinChuyen, $soCoinlucSau, $soCoinLucDau, $tenCoin, $coinFee = null) {
  $db                                 =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $mail                               =       new PHPMailer(true);
  $result                             =       false;
  $today                              =       date("d/m/Y");
  $userNhanEmail                      =       getUserEmail($userNhan);
  $userNhanFullName                   =       getFullName($userNhan);
  $userChuyenFullName                 =       getFullName($userChuyen);

  //$soCoinUserNhanLucDau               =       $soCoin - $soCoinChuyen;
  $coinWithFee                        =       ($soCoinChuyen - ($soCoinChuyen * $coinFee));
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
      $mail->Body    = "Xin chào ".$userNhanFullName."<br />Bạn vừa thực hiện thành công giao dịch chuyển coin vào ngày ".$today." với thông tin như sau: <br />User chuyển: <b>".$userChuyen." (".$userChuyenFullName.")</b><br />Số Coin ban đầu: <b>".$soCoinLucDau." ".$tenCoin."</b><br />Số Coin nhận: <b>".$coinWithFee." ".$tenCoin."</b> (Đã trừ fee ".($coinFee*100)."%)<br />Số Coin sau khi nhận: <b>".$soCoinlucSau." ".$tenCoin."</b><br />Vui lòng kiểm tra bot telegram để xem lại thông tin<br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

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

function calculateAdminFee($transferFee, $roles) {
	$db             =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$soCoinChia 	=		0;
	$arrayQuery     =       $db->query("SELECT `username`, `roles` FROM `users` WHERE `roles` = ':roles'",['roles' => $roles])->fetch_all();
	$numberPeople 	=		count($arrayQuery);
	if($roles == 'admin') {
		$soCoinChia 	=	($transferFee * 0.4)/$numberPeople;
	} else if($roles == 'dev') {
		$soCoinChia 	=	($transferFee * 0.6)/$numberPeople;
	}
	return $soCoinChia;
	$db->close();
}

function updateAdminFee($ngayNhan, $soCoinNhan, $tenCoin, $idGiaoDich) {
  $result         =       false;
  $coinAdminGet   =       0;
  $db             =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $arrayQuery     =       $db->query("SELECT `username`, `roles` FROM `users` WHERE `roles` = ':admin' OR `roles` = ':dev' ",['admin'=> 'admin', 'dev'=> 'dev'])->fetch_all();
  foreach($arrayQuery as $key => $value) {
    $coinAdminGet   =		 calculateAdminFee($soCoinNhan, trim($value['roles']));
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

//Gửi Mail Thêm Coin
function sendEmailAddCoin($userName, $soCoin, $tenCoin, $txtId) {
  $mail                               =       new PHPMailer(true);
  $result                             =       false;
  $today                              =       date("d/m/Y H:i");
  $userEmail                          =       getUserEmail($userName);
  //$userFullName                       =       getFullName($userName);
  $kyhieuCoin                         =       getKyHieuCoin($tenCoin);
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
      $mail->setFrom($userEmail, 'Yêu cầu thêm Coin - TeamTA Telegram Bot');
      $mail->addAddress('ta.team.rb@gmail.com', 'Yêu cầu thêm Coin');

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

      $mail->isHTML(true);

      $mail->Subject = "User: ".$userName." - thêm ".$soCoin." - plan ".$tenCoin;
      $mail->Body    = "Bạn vừa nhận được yêu cầu thêm coin vào ngày ".$today." từ user: <b>".$userName."</b><br />Tên Plan: <b>".$tenCoin."<b/><br />Số coin yêu cầu thêm: <b>".$soCoin." ".$kyhieuCoin."</b><br />Txtid: <b>".$txtId."</b><br />Xin cám ơn !";

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

function insertUserAddCoin($telegramId, $tenPlan, $soCoinThem, $txtId) {
	$result 			  =		  false;
	$db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$currentUser          =       getCurrentUser($telegramId);
  	$userEmail            =       getUserEmail($currentUser);
  	$today                =       date("Y-m-d H:i:s");
  	$queryInfo    		  = 	  $db->query("SELECT `id` FROM `dangkycoin` WHERE `username` = ':username'",['username'=>$currentUser, 'ten_plan'=>$tenPlan, 'so_coin_them'=>$soCoinThem, 'txtid'=>$txtId])->fetch();
  	if(!empty($queryInfo['id'])) {
		$result 		  = 	  $db->update('dangkycoin',['so_coin_them'=>$soCoinThem,'txtid'=>$txtId,'email'=> $userEmail,'ngay_yeu_cau'=>$today]," id = '".$queryInfo['id']."'");
  	} else {
  		$result 		  =		 $db->insert('dangkycoin',['username'=>$currentUser,'ten_plan'=>$tenPlan,'so_coin_them'=>$soCoinThem, 'txtid'=>$txtId, 'email'=>$userEmail, 'ngay_yeu_cau'=>$today]);
  	}
  	return $result;
	$db->close();
}

//Gửi Email Token thay đổi password
function sendEmailChangePassword($telegramId, $password) {
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  
  $mail                 =       new PHPMailer(true);
  $result               =       false;
  $today                =       date("Y-m-d H:i:s");
  $currentUser          =       getCurrentUser($telegramId);
  $userEmail            =       getUserEmail($currentUser);
  $userFullName         =       getFullName($currentUser);
  $tokenCode            =       createRandomToken();
  $password             =       trim($password);
  		$db->update('users',['ngay_yeu_cau'=>$today,'token'=> $tokenCode]," username = '".$currentUser."'");
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

      $mail->Subject = "Code xác nhận thay đổi Password";
      $mail->Body    = "Xin chào ".$userFullName."<br />Bạn đang thực hiện yêu cầu thay đổi Password<br />Password yêu cầu thay đổi: <b>".$password."</b><br />Vui lòng nhập code dưới đây vào bot telegram để xác nhận việc chuyển coin: <b>".$tokenCode."</b><br />Lưu ý: <b>Code có hiệu lực trong vòng 5 phút, yêu cầu của bạn sẽ bị hủy nếu bạn nhập sai code</b><br /> Mọi thắc mắc xin gửi mail về: <a href='mailto:ta.team.rb@gmail.com'>ta.team.rb@gmail.com</a><br />Xin cám ơn !";

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
function checkConfirmPassword($telegramId, $confirmCode) {
  $result   =   false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser          =       getCurrentUser($telegramId);
  $confirmCode          =       trim($confirmCode);
  $arrayQuery           =       $db->query("SELECT `id` FROM :table WHERE `username` LIKE ':username' AND `token` = ':token'",['table'=>'users','username'=> $currentUser, 'token' => $confirmCode])->fetch();

  if(!empty($arrayQuery['id'])) {
      $result   =   true;
  } else {
      //$db->update('users',['token'=> '']," username = '".$currentUser."'");
  }
  return $result;
  $db->close();
}

function updateUserPassword($telegramId, $passwordUser) {
  $result               =       false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser          =       getCurrentUser($telegramId);
  $result               =       $db->update('users',['password'=>$passwordUser,'token'=> '']," username = '".$currentUser."'");
  return $result;
  $db->close();
}

function updateSendMailTimes($telegramId, $sendTimes) {
  $result               =       false;
  $db                   =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $currentUser          =       getCurrentUser($telegramId);
  $result               =       $db->update('users',['sendmail_times'=>$sendTimes]," username = '".$currentUser."'");
  return $result;
  $db->close();
}

// Gửi yêu cầu support
function sendEmailSupport($currentUser, $supportType, $supportContent) {
  $mail                 =       new PHPMailer(true);
  $result               =       false;
  $today                =       date("d-m-Y H:i:s");
  $userEmail            =       getUserEmail($currentUser);
  $userFullName         =       getFullName($currentUser);
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
      if(strtolower($supportType) == 'bot') {
        $mail->addAddress('ngtanthanh90@gmail.com', 'Support: Bot - from Telegram Bot');
        $mail->addAddress('ta.team.rb@gmail.com', 'Support: Bot - from Telegram Bot');
      } else if(strtolower($supportType) == 'profit') {
        $mail->addAddress('visaotrongdem@gmail.com', 'Support: Lãi - from Telegram Bot');
        $mail->addAddress('ta.team.rb@gmail.com', 'Support: Lãi - from Telegram Bot');
      } else if(strtolower($supportType) == 'sheet') {
        $mail->addAddress('visaotrongdem@gmail.com', 'Support: Bảng Tính - from Telegram Bot');
        $mail->addAddress('ta.team.rb@gmail.com', 'Support: Bảng Tính - from Telegram Bot');
      } else if(strtolower($supportType) == 'information') {
        $mail->addAddress('ln_phuoc@yahoo.com', 'Support: Thông Tin Plan - from Telegram Bot');
        $mail->addAddress('ta.team.rb@gmail.com', 'Support: Thông Tin Plan - from Telegram Bot');
      } else if(strtolower($supportType) == 'other') {
        $mail->addAddress('hanghot00@gmail.com', 'Support: Khác - from Telegram Bot');
        $mail->addAddress('ta.team.rb@gmail.com', 'Support: Khác - from Telegram Bot');
      }
      //$mail->addAddress($userEmail, $userFullName);

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/
      $mail->AddReplyTo($userEmail, $userFullName);
      $mail->isHTML(true);

      $mail->Subject = "Yêu cầu support từ user: ".$userFullName ." (".$currentUser.")";
      $mail->Body    = "Xin chào ! <br />Bạn nhận được yêu cầu support của user: <b>".$currentUser."</b><br />Vào ngày <b>".$today."</b><br />Nội dung yêu cầu support: <b>".$supportContent."</b><br />Xin cám ơn !";

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