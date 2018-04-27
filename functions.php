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

// Kiem Tra User và Password để login
function checkLogin($username, $password) {

    $result   =   false;
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);

    $arrayData = $db->query("SELECT * FROM :table WHERE `username` = ':username' AND `password` = ':password'",['table'=>'users','username'=> $username,'password'=> $password ])->fetch();
    
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

// Kiểm tra thông tin Plan của User
function checkDetailPlan($telegramId, $request = null) {
  $db         =   new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $result_plans = $db->query("SELECT :tenplan_chitiet, :tai_dau_tu, :yeu_cau_khac FROM :table_chitiet WHERE (SELECT :username_users FROM :table_users WHERE :telegram_users = ':telegramId') = :username_chitiet",['table_chitiet'=>'chitietplan', 'table_users'=>'users', 'username_users' => 'username', 'telegram_users' => 'telegram_id', 'telegramId' => $telegramId, 'username_chitiet' => 'username', 'tenplan_chitiet' => 'ten_plan', 'tai_dau_tu' => 'tai_dau_tu', 'yeu_cau_khac' => 'yeu_cau_khac'])->fetch_all();
  
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
   
  $arrayResult = $db->query("SELECT c.`ten_plan`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, c.`so_vi`, u.`ho_ten`, l.`ngay_chia_lai`, l.`lai_coin` FROM `chitietplan` AS c LEFT JOIN `users` AS u ON c.`username` = u.`username` LEFT JOIN `chialai` AS l ON c.`username` = l.`username` WHERE u.`telegram_id` = ':telegram_id' AND c.`ten_plan` = ':current_plan' GROUP BY c.`ten_plan` ORDER BY l.`ngay_chia_lai` DESC", ['telegram_id' => $telegramId, 'current_plan' => $currentPlan])->fetch();

  $result         = "Thông tin plan ".(strtoupper($arrayResult['ten_plan']))." của bạn:\nTên Đăng Ký: ".$arrayResult['ho_ten']."\nSố Coin Đào PoS: ".$arrayResult['so_dao_pos']."\nCổ Phần: ".$arrayResult['co_phan']."%\nSố Ví: ".$arrayResult['so_vi']."\nLãi mới nhất ngày ".$arrayResult['ngay_chia_lai'].": ".$arrayResult['lai_coin'];

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