<?php
// Cac phuong thuc telegram
include __DIR__.'/database/config.inc.php'; // Database Config
include __DIR__.'/database/Database.php'; // Class Database

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

  //SELECT c.`ten_plan`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, c.`so_vi`, u.`ho_ten`, l.`ngay_chia_lai`, l.`lai_coin` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` INNER JOIN `chialai` AS l ON c.`ten_plan` = l.`ten_plan` AND c.`username` = l.`username` WHERE u.`telegram_id` = '338838500' AND c.`ten_plan` = 'liza' ORDER BY l.`ngay_chia_lai` DESC

  //SELECT c.`ten_plan`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, c.`so_vi`, u.`ho_ten`, l.`ngay_chia_lai`, l.`lai_coin` FROM `chitietplan` AS c LEFT JOIN `users` AS u ON c.`username` = u.`username` LEFT JOIN `chialai` AS l ON c.`username` = l.`username` WHERE u.`telegram_id` = ':telegram_id' AND c.`ten_plan` = ':current_plan' GROUP BY c.`ten_plan` ORDER BY l.`ngay_chia_lai` DESC
   
  /*$arrayResult = $db->query("SELECT c.`ten_plan`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, c.`so_vi`, u.`ho_ten`, l.`ngay_chia_lai`, l.`lai_coin` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` INNER JOIN `chialai` AS l ON c.`ten_plan` = l.`ten_plan` AND c.`username` = l.`username` WHERE u.`telegram_id` = ':telegram_id' AND c.`ten_plan` = ':current_plan' ORDER BY l.`ngay_chia_lai` DESC LIMIT 1", ['telegram_id' => $telegramId, 'current_plan' => $currentPlan])->fetch();*/

  /*$arrayResult  =   $db->query("SELECT c.`ten_plan`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, c.`so_vi`, u.`ho_ten`, l.`ngay_chia_lai`, l.`lai_coin` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` INNER JOIN `chialai` AS l ON c.`ten_plan` = l.`ten_plan` AND c.`username` = l.`username` WHERE c.`username` = ':username' AND c.`ten_plan` = ':current_plan' ORDER BY l.`ngay_chia_lai` DESC LIMIT 1", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();*/

  $arrayResult  =   $db->query("SELECT * FROM `chitietplan` WHERE `username` = ':username' AND `ten_plan` = ':current_plan'", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();

  /*$date = new DateTime($arrayResult['ngay_chia_lai']);

  $ngaychialai 	=	$date->format('d/m/Y');*/

  $arrayPlanCoins   = $db->query("SELECT `tong_coin`, `ky_hieu_coin` FROM `plans` WHERE `ten_plan` = ':current_plan'", ['ten_plan' => 'ten_plan', 'current_plan' => $currentPlan])->fetch();

  $arrayUser          = $db->query("SELECT `ho_ten` FROM `users` WHERE `username` = ':username'", ['username' => $currentUser])->fetch();

  $arrayChiaLai        = $db->query("SELECT * FROM `chialai` WHERE `username` = ':username' AND `ten_plan` = ':current_plan' ORDER BY `ngay_chia_lai` DESC LIMIT 1", ['username' => $currentUser, 'current_plan' => $currentPlan])->fetch();


  	$result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTên Đăng Ký: ".$arrayUser['ho_ten']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']." ".$arrayPlanCoins['ky_hieu_coin']."\nCổ Phần: ".$arrayResult['co_phan']."%\nLãi mới nhất ngày ".$arrayChiaLai['ngay_chia_lai'].": ".$arrayChiaLai['lai_coin'] . ' '.$arrayPlanCoins['ky_hieu_coin'];

  /*$result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTổng Coin của Plan: ".$arrayPlanCoins['tong_coin']." ".$arrayPlanCoins['ky_hieu_coin']."\nTên Đăng Ký: ".$arrayUser['ho_ten']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']." ".$arrayPlanCoins['ky_hieu_coin']."\nCổ Phần: ".$arrayResult['co_phan']."%\nLãi mới nhất ngày ".$arrayChiaLai['ngay_chia_lai'].": ".$arrayChiaLai['lai_coin'];*/

  //$result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTổng Coin của Plan: \nTên Đăng Ký: ".$arrayResult['ho_ten']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']."\nCổ Phần: ".$arrayResult['co_phan']."%\nSố Ví: ".$arrayResult['so_vi']."\nLãi mới nhất ngày ". $arrayResult['ngay_chia_lai'] ." : ";

  //$result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTổng Coin của Plan: ".$arrayPlanCoins['tong_coin']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']."\nCổ Phần: ".$arrayResult['co_phan']."%\nSố Ví: ".$arrayResult['so_vi'];

  return $result;
  //return implode("-", $result);
  /*echo '<pre>';
  print_r($arrayChiaLai);
  echo '</pre>';*/
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