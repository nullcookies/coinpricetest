<?php
// Cac phuong thuc telegram
include __DIR__.'/database/config.inc.php'; // Database Config
include __DIR__.'/database/Database.php'; // Class Database
include __DIR__.'/settings.php';
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

// Cho phép sửa số ví hay không
function requestActiveWallet($request) {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $result = $db->update('chitietplan',['active_so_vi'=>$request]);
    return $result;
    $db->close();
}

function getStatusWallet() {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $queryData  =   $db->query("SELECT `active_so_vi` FROM :table GROUP BY `active_so_vi`",['table'=>'chitietplan'])->fetch();
    if($queryData['active_so_vi'] == true) {
        $result     =   'Trạng thái hiện tại: <b>Cho</b> phép sửa số ví';
    } else {
        $result     =   'Trạng thái hiện tại: <b>Không</b> cho phép sửa số ví';
    }
    return $result;
    $db->close();
}

//Lay Mang User Tren Google Doc
function getGoogleDocUser() {
    require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    global $sheetBangTinh;

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    $spreadsheet_id = $sheetBangTinh; // Sheet Bảng Tính

    //$spreadsheet_range = 'Buzz kì 6';
    $spreadsheet_range = "user";

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheet_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu
    //$arrayData 	=	array_shift($arrayData);
    reset($arrayData);
	$key = key($arrayData);
	unset($arrayData[$key]);
    $arrayData 	=	array_values($arrayData);

    $resultArray 	=	array();
    foreach ($arrayData as $key => $value) {
    	$resultArray[$key]['username'] 		=		$value['0'];
    	$resultArray[$key]['password'] 		=		$value['1'];
    	$resultArray[$key]['ho_ten'] 		=		$value['2'];
    	$resultArray[$key]['facebook'] 		=		$value['3'];
    	for($i = 0; $i < count($value); $i++) {
    		if($i < 4 || empty($value[$i])) {
    			$resultArray[$key]['telegram_id'] 		=		0;
    			continue;
    		} else {
    				$resultArray[$key]['telegram_id'] 		=		$value[$i];
    				break;
    			
    		}
    	}
    }

    return $resultArray;
}

// Lấy danh sách các plans hiện tại
function getGoogleDocPlans() {
	require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    global $sheetBangTinh;

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    $spreadsheet_id = $sheetBangTinh; // Sheet Bảng Tính

    //$spreadsheet_range = 'Buzz kì 6';
    $spreadsheet_range = "user";

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheet_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu
    for($i = 0; $i < count($arrayData[0]); $i++) {
    	if($i < 4) {
    		unset($arrayData[0][$i]);
    	}
    }
    $arrayData[0] 	=	array_values($arrayData[0]);
    return $arrayData[0];
}

// Lấy thông tin chi tiết các plan của user đang tham gia
function getGoogleDocChiTiet($tenPlan) {
	require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    //global $sheetBangTinh;
    global $sheetDuAn;

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    //$spreadsheetBangTinh_id = 	$sheetBangTinh; // Sheet Bảng Tính
    $spreadsheetDuAn_id 	= 	$sheetDuAn; // Sheet Dự Án

    $spreadsheet_range 		= 	$tenPlan;

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheetDuAn_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu

    for($i = 0; $i< count($arrayData); $i++) {
    	if($i < 10) {
    		unset($arrayData[$i]);
    	}
    }
    array_pop($arrayData);
    $arrayData 		=	array_values($arrayData);
    $arrayResult 	=	array();
    
    foreach($arrayData as $key => $value) {
    	if(empty($value['0'])) {
    		$arrayResult[$key]['telegram_id'] 	=	0;
    	} else {
    		$arrayResult[$key]['telegram_id'] 	=	$value['0'];
    	}
    	$arrayResult[$key]['username'] 			=	$value['1'];
    	$arrayResult[$key]['so_dao_pos'] 		=	$value['3'];
    	$arrayResult[$key]['so_dau_tu'] 		=	$value['4'];

    	$arrayResult[$key]['co_phan'] 			=	rtrim($value['5'], "%");
    	if(isset($value['7'])) {
            $arrayResult[$key]['so_vi']             =   $value['7'];
        } else {
            $arrayResult[$key]['so_vi']             =   '';
        }
    }
    

    return $arrayResult;
}

// Lấy thông tin tái rút tuần và yêu cầu tháng
function getUserRequest($tenPlan) {
	require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    //global $sheetBangTinh;
    global $sheetBangTinh;

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    //$spreadsheetBangTinh_id = 	$sheetBangTinh; // Sheet Bảng Tính
    $spreadsheetBangTinh_id 	= 	$sheetBangTinh; // Sheet Dự Án

    $spreadsheet_range 			= 	$tenPlan;

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheetBangTinh_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu
    for($i = 0; $i< count($arrayData); $i++) {
    	if($i < 10) {
    		unset($arrayData[$i]);
    	}
    }
    $arrayData 		=	array_values($arrayData);
    $resultArray 	=	array();
    foreach ($arrayData as $key => $value) {
    	//$resultArray[$key]['telegram_id'] 	=	$value['0'];
    	if(is_numeric($value['0'])) {
    		$resultArray[$key]['telegram_id'] 	=	$value['0'];
    	} else {
    		$resultArray[$key]['username'] 		=	$value['0'];
    	}

    	if(isset($value['8'])) {
    		$resultArray[$key]['tai_dau_tu'] 	=	$value['8'];
    	} else {
    		$resultArray[$key]['tai_dau_tu'] 	=	'có';
    	}

    	if(isset($value['13'])) {
    		$resultArray[$key]['yeu_cau_khac'] 	=	$value['13'];
    	} else {
    		$resultArray[$key]['yeu_cau_khac'] 	=	'Chưa có yêu cầu';
    	}
    }

    /*echo '<pre>';
    print_r($resultArray);
    echo '</pre>';*/

    return $resultArray;
}

// Thông tin lãi hàng tuần
function getProfitDetail($tenPlan) {
	require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    //global $sheetBangTinh;
    global $sheetDuAn;

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    //$spreadsheetBangTinh_id = 	$sheetBangTinh; // Sheet Bảng Tính
    $spreadsheetDuAn_id 	= 	$sheetDuAn; // Sheet Dự Án

    $spreadsheet_range 		= 	$tenPlan;

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheetDuAn_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu
    for($i = 0; $i< count($arrayData); $i++) {
    	if($i < 9) {
    		unset($arrayData[$i]);
    	}
    }
    $arrayData 		=	array_values($arrayData);
    array_pop($arrayData);
    
    $arrayResult 	=	array();
    foreach ($arrayData as $key => $value) {
    	/*$arrayResult[$key]['username'] 	=	$value['1'];
    	$arrayResult[$key]['username'] 	=	$value['1'];*/
    	if(count($value) < 9) {
            $value['8']     =   '';
        }
        if($key == 0) {
                continue;
            } else {
                $arrayResult[$key]['username']  =   $value['1'];
                foreach($arrayData[0] as $k => $v) {
                    if($k < 8) {
                        continue;
                    } else {
                        $arrayResult[$key][$v]  =   $value[$k];
                    }
                }
            }
    }
    
    return $arrayResult;
}

//Lấy tổng số coin các Plan
function getPlanTotalCoin($tenPlan) {
	require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    //global $sheetBangTinh;
    global $sheetDuAn;

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    //$spreadsheetBangTinh_id = 	$sheetBangTinh; // Sheet Bảng Tính
    $spreadsheetDuAn_id 	= 	$sheetDuAn; // Sheet Dự Án

    $spreadsheet_range 		= 	$tenPlan;

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheetDuAn_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu
    $arrayResult 	=	array();
    $arrayResult[$tenPlan]['ten_plan'] 	=	$tenPlan;
    $arrayResult[$tenPlan]['tong_coin'] =	$arrayData['3']['3'];

   	return $arrayResult;
}

// Update bảng user
function updateTableUser() {
	$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$result 	= 	'';
	$arrayUser 	=	getGoogleDocUser();
	foreach($arrayUser as $key => $value) {
		$queryUser = $db->findByCol('users','username', $value['username']);
		if($queryUser == true) {
			
			$result = $db->update('users',['password'=>$value['password'], 'ho_ten'=>$value['ho_ten'], 'facebook'=>$value['facebook'], 'telegram_id'=>$value['telegram_id']],' username = "'.$value["username"].'"');
			
		} else {
			$result = $db->insert('users',['username'=>trim($value['username']), 'password'=>$value['password'], 'ho_ten'=>$value['ho_ten'], 'facebook'=>$value['facebook'], 'telegram_id'=>$value['telegram_id']]);
		}
	}
	if($result == true) {
		return 'Cập nhật bảng user thành công';
	} else {
		return 'Cập nhật bảng user không thành công';
	}
	$db->close();
}

// Update bảng chi tiết
function updateTableChiTiet() {
	$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayPlans 	=	getGoogleDocPlans();
	$newArray 		=	array();
	/*foreach($arrayPlans as $plan) {
		$arrayChiTiet 	=	getGoogleDocChiTiet($plan);
		$arrayRequest 	=	getUserRequest($plan);
		$newArray		=	
	}*/

	foreach($arrayPlans as $number => $plan) {
		$arrayChiTiet 	=	getGoogleDocChiTiet($plan);
		$arrayRequest 	=	getUserRequest($plan);
		foreach($arrayChiTiet as $key => $value) {
			/*if(isset($arrayRequest[$key]['telegram_id']) && $value['telegram_id'] == $arrayRequest[$key]['telegram_id'] || isset($arrayRequest[$key]['username']) && $value['username'] == $arrayRequest[$key]['username']) {
				$arrayChiTiet[$key]['tai_dau_tu'] 		=	$arrayRequest[$key]['tai_dau_tu'];
				$arrayChiTiet[$key]['yeu_cau_khac'] 	=	$arrayRequest[$key]['yeu_cau_khac'];
			} else {
				continue;
			}*/
			if(isset($arrayRequest[$key]['telegram_id']) && $value['telegram_id'] == $arrayRequest[$key]['telegram_id']) {
				$arrayChiTiet[$key]['tai_dau_tu'] 		=	$arrayRequest[$key]['tai_dau_tu'];
				$arrayChiTiet[$key]['yeu_cau_khac'] 	=	$arrayRequest[$key]['yeu_cau_khac'];
			} elseif(isset($arrayRequest[$key]['username']) && $value['username'] == $arrayRequest[$key]['username']) {
				$arrayChiTiet[$key]['tai_dau_tu'] 		=	$arrayRequest[$key]['tai_dau_tu'];
				$arrayChiTiet[$key]['yeu_cau_khac'] 	=	$arrayRequest[$key]['yeu_cau_khac'];
			} else {
				$arrayChiTiet[$key]['tai_dau_tu'] 		=	"có";
				$arrayChiTiet[$key]['yeu_cau_khac'] 	=	"Chưa có yêu cầu";
				continue;
			}

		}

			$newArray[$number][$plan]	=	$arrayChiTiet;

	}	// End foreach plan

	/*echo '<pre>';
	print_r($newArray);
	echo '</pre>';*/

	for($i = 0; $i < count($newArray); $i++) {
		foreach ($newArray[$i] as $plan => $details) {
			foreach($details as $key => $value) {
				$queryDetail = $db->query("SELECT * FROM :table WHERE :username = ':username_value' AND :ten_plan = ':ten_plan_value'",['table'=>'chitietplan','username'=>'username','username_value'=>$value['username'], 'ten_plan'=>'ten_plan', 'ten_plan_value' => $plan])->fetch();
				if($queryDetail == true) {			
					$result = $db->update('chitietplan',['so_dao_pos'=>$value['so_dao_pos'], 'so_dau_tu'=>$value['so_dau_tu'], 'co_phan'=>$value['co_phan'], 'so_vi'=>$value['so_vi'], 'tai_dau_tu'=>$value['tai_dau_tu'], 'yeu_cau_khac'=>$value['yeu_cau_khac']],' username = "'.$value['username'].'" AND ten_plan = "'.$plan.'"');
				} else {
					$result = $db->insert('chitietplan',['username'=>$value['username'], 'ten_plan'=>$plan, 'so_dao_pos'=>$value['so_dao_pos'], 'so_dau_tu'=>$value['so_dau_tu'], 'co_phan'=>$value['co_phan'], 'so_vi'=>$value['so_vi'], 'tai_dau_tu'=>$value['tai_dau_tu'], 'yeu_cau_khac'=>$value['yeu_cau_khac']]);
				}
			}
		}
	}

	if($result == true) {
		return "Cập nhật bảng chi tiết thành công";
	} else {
		return "Cập nhật bảng chi tiết không thành công";
	}

	$db->close();
}

// Update bảng chia lãi
function updateTableChiaLai() {
	$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayPlans 	=	getGoogleDocPlans();
	$arrayProfit 	=	array();
	foreach ($arrayPlans as $key => $plan) {
		$arrayProfit 	=	getProfitDetail($plan);
		foreach($arrayProfit as $k => $v) {
			$username 	=	array_shift($v);
			foreach($v as $date => $profit) {

				$queryDetail = $db->query("SELECT * FROM `chialai` WHERE `username` = ':username_value' AND `ten_plan` = ':ten_plan_value' AND `ngay_chia_lai` = ':ngay_chia_lai_value' AND `lai_coin` = ':lai_coin'",['table'=>'chialai','username_value'=>$username, 'ten_plan_value' => $plan, 'ngay_chia_lai_value' => $date, 'lai_coin'=>$profit])->fetch();
				

                if(count($queryDetail) > 0) {
                    $result = $db->update('chialai',['lai_coin'=>$profit],' username = "'.$username.'" AND ten_plan = "'.$plan.'" AND ngay_chia_lai = "'.$date.'"');
                } else {
                    $result = $db->insert('chialai',['username'=>$username, 'ten_plan'=>$plan, 'ngay_chia_lai'=>$date, 'lai_coin'=>$profit]);
                }
			}
		}
	}
    
	if($result == true) {
		return "Cập nhật bảng chia lãi thành công";
	} else {
		return "Cập nhật bảng chia lãi không thành công";
	}

    /*return $queryDetail;*/
	
	$db->close();
}

//Update Bảng Plans
function updateTablePlans() {
	$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
	$arrayPlans 		=	getGoogleDocPlans();
	$arrayTotalCoin 	=	array();
	foreach ($arrayPlans as $key => $plan) {
		$arrayTotalCoin 	=	getPlanTotalCoin($plan);
		foreach($arrayTotalCoin as $ten_plan => $value) {
			$queryPlan = $db->findByCol('plans','ten_plan', strtolower($plan));
			if($queryPlan == true) {
			
				$result = $db->update('plans',['tong_coin'=>$value['tong_coin']],' ten_plan = "'.strtolower($plan).'"');
				
			} else {
				$result = $db->insert('plans',['ten_plan'=> strtolower($plan),'tong_coin'=>$value['tong_coin']]);
			}
		}
	}

	if($result == true) {
		return "Cập nhật bảng Plans thành công";
	} else {
		return "Cập nhật bảng Plans không thành công";
	}
	
	$db->close();
}
?>