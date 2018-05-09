<?php
// Cac phuong thuc telegram
include __DIR__.'/database/config.inc.php'; // Database Config
include __DIR__.'/database/Database.php'; // Class Database
$sheetBangTinh 	=	'1swzaqa9eRT8qRAeKpRnSCAQ2VIotyfiuJK03LpQNYEE'; // Test
$sheetDuAn 		=	'1M17CS2GJy_ibHL0Rn2AcmDQFBLiRl0F1pj9bue6piQE'; // Test
/*$sheetBangTinh 	=	'1NgZq41xShwrIkxDxX5XpWlI7QL0D8npnfN7slj_gIK0';
$sheetDuAn 		=	'1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';*/

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
    $queryUser = $db->query("SELECT `username`, `password`, `ho_ten`, `facebook` FROM :table",['table'=>'users'])->fetch_all();
    
    foreach($queryUser as $key => $value) {
        $queryChitiet   =   $db->query("SELECT c.`username`, c.`ten_plan`, u.`telegram_id` FROM `chitietplan` AS c INNER JOIN `users` AS u ON c.`username` = u.`username` WHERE c.`username` = ':username' AND c.`so_dao_pos` NOT LIKE '0.%'",['table'=>'users', 'username'=>$value['username']])->fetch_all();
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
            if($arrayPlan[$i]['ten_plan'] == 'bullcoin') {
                continue;
            } else {
                if(array_key_exists($arrayPlan[$i]['ten_plan'], $value)) {
                    continue;
                } else {
                    $queryUser[$key][$arrayPlan[$i]['ten_plan']]   =   '';
                }
            }
            
        }
    }
    
    return $queryUser;
    $db->close();
}

function getDbPlans() {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $arrayPlans     =   array();
    $queryPlan = $db->query("SELECT `ten_plan` FROM :table",['table'=>'plans'])->fetch_all();

    /*echo '<pre>';
    print_r($queryPlan);
    echo '<pre>';*/
    return $queryPlan;
    $db->close();
}

//$userId, $tenPlan, $updateText, $updateType
function updateRequest() {

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
      //foreach($arrayData as $k => $v) {
        /*if(in_array($value['username'], $v)) {*/
              /*$valueRange->setValues(["values" => $value]);
              $conf = ["valueInputOption" => "RAW"];
              $updateRange  =   $spreadsheet_range.'!a'.($key+2);
              $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);    */        
          /*} else {
            continue;
          }*/
          
      //}
      $updateArray  =   array();
      foreach($value as $k => $v) {
        $updateArray["values"][]     =   $v;
      }


      $valueRange->setValues($updateArray);
      $conf = ["valueInputOption" => "RAW"];
      $updateRange  =   $spreadsheet_range.'!a'.($key+2);
      $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
      /*$valueRange->setValues(["values" => ['aaaa']]);
          $conf = ["valueInputOption" => "RAW"];*/
          /*$updateRange  =   $spreadsheet_range.'!a'.($key+1);
          $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);*/
      $status   =   true;
    }
    /*
    $valueRange->setValues(["values" => [$updateText]]);
          $conf = ["valueInputOption" => "RAW"];
          if($updateType == 'rut_tuan') {
            $updateRange  =$spreadsheet_range.'!a'.($key+1);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            $status   = true;
            break;
          } else if($updateType == 'rut_thang') {
            $updateRange  =$spreadsheet_range.'!n'.($key+1);
            $service->spreadsheets_values->update($spreadsheet_id, $updateRange, $valueRange, $conf);
            $status   = true;
            break;
          }
    */
    return $status;

}
?>