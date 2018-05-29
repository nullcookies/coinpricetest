<?php
// Cac phuong thuc telegram
include __DIR__.'/../database/config.inc.php'; // Database Config
include __DIR__.'/../database/Database.php'; // Class Database
include __DIR__.'/../settings.php';

function siteUrl() {
    // base directory
    $base_dir = __DIR__;

    // server protocol
    $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';

    // domain name
    $domain = $_SERVER['SERVER_NAME'];

    // base url
    //$base_url = preg_replace("!^${doc_root}!", '', $base_dir);

    // server port
    $port = $_SERVER['SERVER_PORT'];
    $disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";

    // put em all together to get the complete base URL
    $url = "${protocol}://${domain}${disp_port}";

    return $url;
}

function replace_key($arr, $oldkey, $newkey) {
    if(array_key_exists( $oldkey, $arr)) {
        $keys = array_keys($arr);
        $keys[array_search($oldkey, $keys)] = $newkey;
        return array_combine($keys, $arr);  
    }
    return $arr;    
}

function getDbUser() {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $arrayResult    =   array();
    $queryUser = $db->query("SELECT `username`, `password`, `ho_ten`, `facebook`, `telegram_id`, `email` FROM :table",['table'=>'users'])->fetch_all();
    
    /*foreach($queryUser as $key => $value) {
        $queryChitiet   =   $db->query("SELECT c.`username`, c.`ten_plan`, u.`telegram_id` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` WHERE c.`username` = ':username' AND c.`so_dao_pos` NOT LIKE '0.00000%'",['table'=>'users', 'username'=>$value['username']])->fetch_all();
        foreach($queryChitiet as $k => $v) {
            if($v['telegram_id'] == 0) {
                $v['telegram_id']  =    '';
            }
            if(in_array($value['username'], $queryChitiet[$k])) {
                $queryUser[$key][$v['ten_plan']]   =  $v['telegram_id'];  
            } else {
                continue;
            }
        }
        
    }

    $arrayPlan  =   getDbPlans();
    for($i = 0; $i < count($arrayPlan); $i++) {
        foreach($queryUser as $key => $value) {
            if(array_key_exists($arrayPlan[$i]['ten_plan'], $value)) {
                continue;
            } else {
                $queryUser[$key][$arrayPlan[$i]['ten_plan']]   =   '';
            }
            
        }
    }*/
    
    return $queryUser;
    $db->close();
}

function getDbPlans() {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $arrayPlans     =   array();
    $queryPlan = $db->query("SELECT `ten_plan` FROM :table WHERE `ten_plan` != 'bullcoin'",['table'=>'plans'])->fetch_all();

    return $queryPlan;
    $db->close();
}

//Push Data lên bảng user trong sheet Bang Tinh
function updateUserSheet() {

  require 'vendor/autoload.php';

  global $sheetBangTinh;

  $service_account_file = 'client_services.json';

  $spreadsheet_id = $sheetBangTinh;

  $spreadsheet_range = 'user';

  $status   = false;

  putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);
  $client = new Google_Client();
  $client->useApplicationDefaultCredentials();
  $client->addScope(Google_Service_Sheets::SPREADSHEETS);
  $service = new Google_Service_Sheets($client);

  $result = $service->spreadsheets_values->get($spreadsheet_id, $spreadsheet_range);

  $valueRange= new Google_Service_Sheets_ValueRange($client);
  //$valueRange->setValues(["values" => [$updateText]]);
  //$conf = ["valueInputOption" => "RAW"];
  $arrayData    =   $result->getValues();
  $arrayUser    =   getDbUser();
  $arrayKeys    =   array_flip($arrayData[0]);
  $arrayKeys    =   replace_key($arrayKeys, 'User', 'username');
  $arrayKeys    =   replace_key($arrayKeys, 'Pass', 'password');
  $arrayKeys    =   replace_key($arrayKeys, 'Tên', 'ho_ten');
  $arrayKeys    =   replace_key($arrayKeys, 'Facebook', 'facebook');


  foreach($arrayUser as $k => $v) {
    $arrayUser[$k]      =   array_replace($arrayKeys, $v);
  }

  array_shift($arrayData);
  
  foreach($arrayUser as $key => $value) {
      $updateArray  =   array();
      foreach($value as $k => $v) {
        $updateArray["values"][]     =   $v;
      }
      $valueRange->setValues($updateArray);
      $conf = ["valueInputOption" => "RAW"];
      $updateRange  =   $spreadsheet_range.'!a'.($key+2);
      $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
      $status   =   true;
    }
    
    return $status;

}

// Lấy mảng Plan trên Google Sheet
function getGooglePlanData($tenPlan) {
  require 'vendor/autoload.php';

  global $sheetDuAn;

  $service_account_file = 'client_services.json';

  $spreadsheet_id = $sheetDuAn;

  $spreadsheet_range = $tenPlan;

  $status   = false;

  putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);
  $client = new Google_Client();
  $client->useApplicationDefaultCredentials();
  $client->addScope(Google_Service_Sheets::SPREADSHEETS);
  $service = new Google_Service_Sheets($client);

  $result = $service->spreadsheets_values->get($spreadsheet_id, $spreadsheet_range);
  $valueRange= new Google_Service_Sheets_ValueRange($client);
  $arrayData    =   $result->getValues();
  for($i = 0; $i < count($arrayData); $i++) {
    if($i < 10) {
        unset($arrayData[$i]);
    } else {
        break;
    }
  }
  $arrayData = array_values($arrayData);

  /*echo '<pre>';
  print_r($arrayData);
  echo '</pre>';*/
  return $arrayData;

}

function getDataChiTiet($tenPlan) {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $queryUser = $db->query("SELECT `username` FROM :table WHERE `ten_plan` = ':ten_plan'",['table'=>'chitietplan', 'ten_plan'=>$tenPlan])->fetch_all();
    foreach($queryUser as $key => $value) {
        //$queryData[$key] = $db->query("SELECT c.`username`, u.`ho_ten`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, u.`facebook`, c.`so_vi` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` WHERE c.`username` = ':username' AND `ten_plan` = ':ten_plan'",['username'=>$value['username'], 'ten_plan'=>$tenPlan])->fetch();
        $queryData[$key] = $db->query("SELECT c.`username`, u.`ho_ten`, c.`so_dao_pos`, c.`so_dau_tu`, c.`co_phan`, u.`facebook`, c.`so_vi` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` WHERE c.`username` = ':username' AND `ten_plan` = ':ten_plan'",['username'=>$value['username'], 'ten_plan'=>$tenPlan])->fetch();
        
        
    }
    /*echo '<pre>';
    print_r($queryData);
    echo '</pre>';*/
    return $queryData;
    $db->close();
}

function getDataChiaLai($userName, $tenPlan) {
  $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $queryChiaLai   =   $db->query("SELECT `ngay_chia_lai`, `lai_coin` FROM `chialai` WHERE `username` = ':username' AND `ten_plan` = ':ten_plan' ORDER BY `ngay_chia_lai` DESC LIMIT 1 ",['username'=>$userName,'ten_plan'=>$tenPlan])->fetch();
  /*echo '<pre>';
    print_r($queryChiaLai);
    echo '</pre>';*/
  return $queryChiaLai;
  $db->close();
}

function updatePlansSheet($tenPlan, $arrayUpdate, $updateType = 'all') {
    require 'vendor/autoload.php';

    global $sheetDuAn;

    $service_account_file = 'client_services.json';

    $spreadsheet_id = $sheetDuAn;

    $spreadsheet_range = $tenPlan;

    $status   = false;

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS);
    $service = new Google_Service_Sheets($client);

    $valueRange= new Google_Service_Sheets_ValueRange($client);

    $arrayGooglePlan    =   getGooglePlanData($tenPlan);
    $totalRows          =   count($arrayGooglePlan);

    if($updateType == 'all') {
      foreach($arrayUpdate as $key => $value) {
        foreach($arrayGooglePlan as $a => $b) {
          if(in_array($value['username'], $b)) {
              $updateArray  =   array();
            foreach($value as $k => $v) {
              if($k == 'username' || $k == 'ho_ten' || $k == 'so_dao_pos') {
                if($k == 'so_dao_pos') {
                    $v  =   doubleval($v);
                }
                  $updateArray["values"][]     =   $v;
              } else {
                continue;
              }
            }
            $valueRange->setValues($updateArray);
            $conf = ["valueInputOption" => "RAW"];
            $updateRange  =   $spreadsheet_range.'!b'.($a+11).':e'.($a+11);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            $arrayKeys[]    =   $key;
            unset($updateArray["values"]);
            sleep(1);
            $status   =   true;
          } else {
              continue;
          }
        }
      }

      // Facebook và Số Ví
      foreach($arrayUpdate as $key => $value) {
        foreach($arrayGooglePlan as $a => $b) {
          if(in_array($value['username'], $b)) {
              $updateArray  =   array();
            foreach($value as $k => $v) {
              if($k == 'facebook' || $k == 'so_vi') {
                  $updateArray["values"][]     =   $v;
              } else {
                continue;
              }
            }
            $valueRange->setValues($updateArray);
            $conf = ["valueInputOption" => "RAW"];
            $updateRange  =   $spreadsheet_range.'!g'.($a+11).':h'.($a+11);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            unset($updateArray["values"]);
            sleep(1);
            $status   =   true;
          } else {
              continue;
          }
        }
      }
    } else if($updateType == 'ho_ten') { // Họ Tên
      foreach($arrayUpdate as $key => $value) {
        foreach($arrayGooglePlan as $a => $b) {
          if(in_array($value['username'], $b)) {
              $updateArray  =   array();
            foreach($value as $k => $v) {
              if($k == 'ho_ten') {
                  $updateArray["values"][]     =   $v;
              } else {
                continue;
              }
            }
            $valueRange->setValues($updateArray);
            $conf = ["valueInputOption" => "RAW"];
            $updateRange  =   $spreadsheet_range.'!c'.($a+11);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            $arrayKeys[]    =   $key;
            unset($updateArray["values"]);
            sleep(1);
            $status   =   true;
          } else {
              continue;
          }
        }
      }
    } else if($updateType == 'so_dao_pos') { // Số Đào Pos
      foreach($arrayUpdate as $key => $value) {
        foreach($arrayGooglePlan as $a => $b) {
          if(in_array($value['username'], $b)) {
              $updateArray  =   array();
            foreach($value as $k => $v) {
              if($k == 'so_dao_pos') {
                  $v  =   doubleval($v);
                  $updateArray["values"][]     =   $v;
              } else {
                continue;
              }
            }
            $valueRange->setValues($updateArray);
            $conf = ["valueInputOption" => "RAW"];
            $updateRange  =   $spreadsheet_range.'!d'.($a+11);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            $arrayKeys[]    =   $key;
            unset($updateArray["values"]);
            sleep(1);
            $status   =   true;
          } else {
              continue;
          }
        }
      }
    } else if($updateType == 'facebook') { // Facebook
      foreach($arrayUpdate as $key => $value) {
        foreach($arrayGooglePlan as $a => $b) {
          if(in_array($value['username'], $b)) {
              $updateArray  =   array();
            foreach($value as $k => $v) {
              if($k == 'facebook') {
                  $updateArray["values"][]     =   $v;
              } else {
                continue;
              }
            }
            $valueRange->setValues($updateArray);
            $conf = ["valueInputOption" => "RAW"];
            $updateRange  =   $spreadsheet_range.'!g'.($a+11);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            unset($updateArray["values"]);
            sleep(1);
            $status   =   true;
          } else {
              continue;
          }
        }
      }
    } else if($updateType == 'so_vi') { // Số Ví
      foreach($arrayUpdate as $key => $value) {
        foreach($arrayGooglePlan as $a => $b) {
          if(in_array($value['username'], $b)) {
              $updateArray  =   array();
            foreach($value as $k => $v) {
              if($k == 'so_vi') {
                  $updateArray["values"][]     =   $v;
              } else {
                continue;
              }
            }
            $valueRange->setValues($updateArray);
            $conf = ["valueInputOption" => "RAW"];
            $updateRange  =   $spreadsheet_range.'!h'.($a+11);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            unset($updateArray["values"]);
            sleep(1);
            $status   =   true;
          } else {
              continue;
          }
        }
      }
    }
    /*// Update Ngay Chia Lai
    foreach($arrayUpdate as $key => $value) {
            $updateArray    =   array();
            $arrayChiaLai   =   getDataChiaLai($value['username'], $tenPlan);
            foreach($arrayChiaLai as $k => $v) {
              if($k == 'ngay_chia_lai') {
                $updateArray["values"][]     =   $v;
              }
              break;
            }
          $valueRange->setValues($updateArray);
          $conf = ["valueInputOption" => "RAW"];
          $updateRange  =   $spreadsheet_range.'!i10';
          $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
          sleep(1);
          break;
    }

    // Update Coin Chia Lai
    foreach($arrayUpdate as $key => $value) {
      foreach($arrayGooglePlan as $a => $b) {
        if(in_array($value['username'], $b)) {
            $updateArray    =   array();
            $arrayChiaLai   =   getDataChiaLai($value['username'], $tenPlan);
            if(!empty($arrayChiaLai)) {
              foreach($arrayChiaLai as $k => $v) {
                if($k == 'lai_coin') {
                  $updateArray["values"][]     =   doubleval($v);
                }
              }
            } else {
              $updateArray["values"][]     =   doubleval('0.00000000');
            }
          
          $valueRange->setValues($updateArray);
          $conf = ["valueInputOption" => "RAW"];
          $updateRange  =   $spreadsheet_range.'!i'.($a+11);
          $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
          unset($updateArray["values"]);
          sleep(1);
          $status   =   true;
        } else {
            continue;
        }
      }
    }*/

    // Insert Thêm User Mới
    $countNewUser   =   0;
    foreach($arrayUpdate as $key => $value) {
      if(in_array($key, $arrayKeys)) {
        continue;
      } else {
        foreach($value as $k => $v) {
          if($k == 'username' || $k == 'ho_ten' || $k == 'so_dao_pos' || $k == 'so_dau_tu') {
            if($k == 'so_dao_pos' || $k == 'so_dau_tu') {
                $v  =   doubleval($v);
            }
              $updateArray["values"][]     =   $v;
          } else {
            continue;
          }
        }
        $valueRange->setValues($updateArray);
        $conf = ["valueInputOption" => "RAW"];
        $updateRange  =   $spreadsheet_range.'!b'.($totalRows+$countNewUser+11).':e'.($totalRows+$countNewUser+11); 
        $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
        $countNewUser++;
        sleep(1);
        unset($updateArray["values"]);
        $status   =   true;
      }
    }

    // Facebook va So Vi
    $countNewUser   =   0;
    foreach($arrayUpdate as $key => $value) {
      if(in_array($key, $arrayKeys)) {
        continue;
      } else {
        foreach($value as $k => $v) {
          if($k == 'facebook' || $k == 'so_vi') {
              $updateArray["values"][]     =   $v;
          } else {
            continue;
          }
        }
        $valueRange->setValues($updateArray);
        $conf = ["valueInputOption" => "RAW"];
        $updateRange  =   $spreadsheet_range.'!g'.($totalRows+$countNewUser+11).':h'.($totalRows+$countNewUser+11); 
        $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
        $countNewUser++;
        sleep(1);
        unset($updateArray["values"]);
        $status   =   true;
      }
    }

    /*// Lãi Coin
    $countNewUser   =   0;
    foreach($arrayUpdate as $key => $value) {
      if(in_array($key, $arrayKeys)) {
        continue;
      } else {
        $arrayChiaLai   =   getDataChiaLai($value['username'], $tenPlan);
        if(!empty($arrayChiaLai)) {
          foreach($arrayChiaLai as $k => $v) {
            if($k == 'lai_coin') {
              $updateArray["values"][]     =   doubleval($v);
            }
          }
        } else {
          $updateArray["values"][]     =   doubleval('0.00000000');
        }
        
        $valueRange->setValues($updateArray);
        $conf = ["valueInputOption" => "RAW"];
        $updateRange  =   $spreadsheet_range.'!i'.($totalRows+$countNewUser+11); 
        $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
        $countNewUser++;
        sleep(1);
        unset($updateArray["values"]);
        $status   =   true;
      }
    }*/

    return $status;
}

function callUpdatePlans($tenPlan, $updateType = 'all') {
    $status   = false;
    //$arrayPlans  =   getDbPlans();
    /*foreach($arrayPlans as $key => $value) {
        $arrayCurrentPlan   =   getDataChiTiet($value['ten_plan']);
        updatePlansSheet($value['ten_plan'], $arrayCurrentPlan);
    }*/
    $arrayCurrentPlan   =   getDataChiTiet($tenPlan);
    $status   =   updatePlansSheet($tenPlan, $arrayCurrentPlan);
    if($status == true) {
      return 'Cập nhật bảng '.$tenPlan.' thành công';
    } else {
      return 'Cập nhật bảng '.$tenPlan.' không thành công';
    }
}
?>