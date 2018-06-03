<?php
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
switch ($step) {
	case '1':
	  setData('username-'.A_USER_CHAT_ID,$text);
	  $sendMessage->chat_id = A_USER_CHAT_ID;
	  $sendMessage->text = 'Vui lòng nhập Password của bạn:';
	  setData('step-'.A_USER_CHAT_ID,'2');
	  break;
	case '2':
	  setData('password-'.A_USER_CHAT_ID, $text);
	  $username   =   getData('username-'.A_USER_CHAT_ID);
	  $password   =   getData('password-'.A_USER_CHAT_ID);
	  if(checkLogin($username, $password) == true) {
	    insertTelegramId($username, A_USER_CHAT_ID);
	    require_once __DIR__.'/types/init_keyboards.php';
	    removeData('username-'.A_USER_CHAT_ID);
	    removeData('password-'.A_USER_CHAT_ID);
	    setData('step-'.A_USER_CHAT_ID,'0');
	    setData('verified-'.A_USER_CHAT_ID,'yes');
	  } else {
	    $sendMessage->chat_id = A_USER_CHAT_ID;
	    $sendMessage->text = 'Đăng nhập không thành công ! Vui lòng nhấn /start để đăng nhập lại';
	    setData('step-'.A_USER_CHAT_ID,'0');
	    setData('verified-'.A_USER_CHAT_ID,'no');
	  }
	  break;
	default:
	    /*$verifiedUser   =   getData('verified-'.A_USER_CHAT_ID);
	    if($verifiedUser == 'no') {
	      $sendMessage->chat_id = A_USER_CHAT_ID;
	      $sendMessage->text = 'Vui lòng nhấn /start để đăng nhập';
	    } else {
	      $sendMessage->chat_id = A_USER_CHAT_ID;
	      $sendMessage->text = 'Yêu cầu của bạn không được xử lý, vui lòng thử lại';
	    }*/
	    $sendMessage->chat_id = A_USER_CHAT_ID;
	    $sendMessage->text = 'Yêu cầu của bạn không được xử lý, vui lòng thử lại';
	  break;
	} // End Switch Step Đăng Nhập

	switch ($stepRegister) {
		case '1':
		  if(is_numeric($text)) {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng chọn Username không phải là số, nhập lại username khác:';
		    setData('step-register-'.A_USER_CHAT_ID,'1');
		  } elseif(checkUserExisting($text) == true) {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Username bạn đăng ký đã tồn tại, vui lòng chọn username khác:';
		    setData('step-register-'.A_USER_CHAT_ID,'1');
		  } else {
		    setData('username-register-'.A_USER_CHAT_ID,$text);
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng nhập Password của bạn:';
		    setData('step-register-'.A_USER_CHAT_ID,'2');
		  }
		  break;
		case '2':
		  if($text == '') {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng không để trống Password !';
		    setData('step-register-'.A_USER_CHAT_ID,'2');
		  } else {
		    setData('password-register-'.A_USER_CHAT_ID,$text);
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng nhập Họ Tên của bạn:';
		    setData('step-register-'.A_USER_CHAT_ID,'3');
		  }
		  break;
		case '3':
		  if($text == '') {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng không để trống Họ Tên !';
		    setData('step-register-'.A_USER_CHAT_ID,'3');
		  } else {
		    setData('fullname-register-'.A_USER_CHAT_ID,$text);
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng nhập Facebook của bạn:';
		    setData('step-register-'.A_USER_CHAT_ID,'4');
		  }
		  break;
		case '4':
		  if($text == '') {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng không để trống Facebook !';
		    setData('step-register-'.A_USER_CHAT_ID,'4');
		  } else {
		    setData('facebook-register-'.A_USER_CHAT_ID,$text);
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Vui lòng nhập Email của bạn:';
		    setData('step-register-'.A_USER_CHAT_ID,'5');
		  }
		  break;
		case '5':
		  if(filter_var($text, FILTER_VALIDATE_EMAIL)) {
		    setData('email-register-'.A_USER_CHAT_ID,$text);
		    $registerUser         =   vn_to_str(getData('username-register-'.A_USER_CHAT_ID));
		    $registerPassword     =   vn_to_str(getData('password-register-'.A_USER_CHAT_ID));
		    $registerFullname     =   getData('fullname-register-'.A_USER_CHAT_ID);
		    $registerFacebook     =   getData('facebook-register-'.A_USER_CHAT_ID);
		    $registerEmail        =   getData('email-register-'.A_USER_CHAT_ID);
		    $sendMessage->chat_id =   A_USER_CHAT_ID;
		    $sendMessage->text    =   "Vui lòng xác nhận những thông tin bạn đã đăng ký dưới đây:\nUsername: ".strtolower($registerUser)."\nPassword: ".$registerPassword."\nHọ Tên: ".$registerFullname."\nFacebook: ".$registerFacebook."\nEmail: ".$registerEmail."\nVui lòng chọn 'yes' để xác nhận hoặc 'no' để hủy thông tin và nhập lại";
		    setData('step-register-'.A_USER_CHAT_ID,'6');  
		  } elseif(checkEmailExisting($text)) {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Email này đã được đăng ký, vui lòng chọn email khác !';
		    setData('step-register-'.A_USER_CHAT_ID,'5');
		  } else {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Email bạn nhập không đúng, vui lòng nhập lại !';
		    setData('step-register-'.A_USER_CHAT_ID,'5');
		  }
		  break;
		case '6':
		  switch (strtolower($text)) {
		    case 'yes':
		      $resultText   =   '';
		      $registerUser         =   vn_to_str(getData('username-register-'.A_USER_CHAT_ID));
		      $registerPassword     =   vn_to_str(getData('password-register-'.A_USER_CHAT_ID));
		      $registerFullname     =   getData('fullname-register-'.A_USER_CHAT_ID);
		      $registerFacebook     =   getData('facebook-register-'.A_USER_CHAT_ID);
		      $registerEmail        =   getData('email-register-'.A_USER_CHAT_ID);
		      $sendMessage->chat_id =   A_USER_CHAT_ID;
		      $result         = insertNewUser(strtolower($registerUser), $registerPassword, $registerFullname, $registerFacebook, A_USER_CHAT_ID , $registerEmail);
		      if($result == true) {
		        sendRegisterMail(strtolower($registerUser), $registerPassword, $registerFullname, $registerFacebook, $registerEmail);
		        $resultText     = 'Đăng ký thành công, vui lòng Đăng Nhập.';  
		      }
		      $sendMessage->text    =   $resultText;
		      setData('step-register-'.A_USER_CHAT_ID,'0');
		      removeData('username-register-'.A_USER_CHAT_ID);
		      removeData('password-register-'.A_USER_CHAT_ID);
		      removeData('fullname-register-'.A_USER_CHAT_ID);
		      removeData('facebook-register-'.A_USER_CHAT_ID);
		      removeData('email-register-'.A_USER_CHAT_ID);
		      break;
		    
		    case 'no':
		      $sendMessage->chat_id =   A_USER_CHAT_ID;
		      $sendMessage->text    =   "Thông tin đã hủy, vui lòng nhấn nút Đăng Ký để nhập lại.";
		      setData('step-register-'.A_USER_CHAT_ID,'0');
		      removeData('username-register-'.A_USER_CHAT_ID);
		      removeData('password-register-'.A_USER_CHAT_ID);
		      removeData('fullname-register-'.A_USER_CHAT_ID);
		      removeData('facebook-register-'.A_USER_CHAT_ID);
		      removeData('email-register-'.A_USER_CHAT_ID);
		      break;
		  }
		  break;
		default:
		  
		  break;
	} // End Switch Step Đăng Ký

	switch ($stepWallet) {
		case '1':
		  setData('plan-wallet-'.A_USER_CHAT_ID,$text);
		  $sendMessage->chat_id = A_USER_CHAT_ID;
		  $sendMessage->text = 'Vui lòng nhập Số ví bạn muốn thay đổi (lưu ý nếu nhập sai số ví chúng tôi sẽ không chịu trách nhiệm)';
		  setData('change-wallet-step-'.A_USER_CHAT_ID,'2');
		  break;
		case '2':
		  setData('wallet-'.A_USER_CHAT_ID,$text);
		  $requestPlan    =   getData('plan-wallet-'.A_USER_CHAT_ID);
		  $requestWallet  =   getData('wallet-'.A_USER_CHAT_ID);
		  if(checkUserWallet($text)) {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Số ví này đã được đăng ký, vui lòng nhập lại số ví !';
		    setData('change-wallet-step-'.A_USER_CHAT_ID,'2');
		  } else {
		    if(checkUserPlan(A_USER_CHAT_ID, $requestPlan)) {
		      $sendMessage->chat_id = A_USER_CHAT_ID;
		      $sendMessage->text = insertUserInfo(A_USER_CHAT_ID, $requestWallet, 'so_vi', $requestPlan);
		      removeData('plan-wallet-'.A_USER_CHAT_ID);
		      removeData('wallet-'.A_USER_CHAT_ID);
		      setData('change-wallet-step-'.A_USER_CHAT_ID,'0');
		    } else {
		      $sendMessage->chat_id = A_USER_CHAT_ID;
		      $sendMessage->text = 'Bạn chưa tham gia Plan '. $requestPlan .' hoặc nhập sai tên Plan, vui lòng nhấn nút Sửa Số Ví để nhập lại';
		      setData('change-wallet-step-'.A_USER_CHAT_ID,'0');
		    }
		  }
		  break;
		default:

		  break;
	} // End Change Email

	switch ($stepEmail) {
		case '1':
		  if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = insertUserInfo(A_USER_CHAT_ID, $text, 'email');

		    //removeData('email-'.A_USER_CHAT_ID);
		    setData('change-email-step-'.A_USER_CHAT_ID,'0');
		  } else {
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = 'Email bạn nhập không đúng, vui lòng nhấn nút Sửa Email để nhập lại...';
		    //removeData('email-'.A_USER_CHAT_ID);
		    setData('change-email-step-'.A_USER_CHAT_ID,'0');
		  }
		  break;
		default:

		  break;
	} // End Change Email

	switch ($stepFullname) {
		case '1':
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = insertUserInfo(A_USER_CHAT_ID, $text, 'ho_ten');
		    //removeData('email-'.A_USER_CHAT_ID);
		    setData('change-fullname-step-'.A_USER_CHAT_ID,'0');
		  break;
		default:

		  break;
	} // End Change Họ Tên

	switch ($stepFacebook) {
		case '1':
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = insertUserInfo(A_USER_CHAT_ID, $text, 'facebook');
		    //removeData('email-'.A_USER_CHAT_ID);
		    setData('change-facebook-step-'.A_USER_CHAT_ID,'0');
		  break;
		default:

		  break;
	} // End Change Facebook

	switch ($stepPassword) {
		case '1':
			$text 		=		vn_to_str($text);
			setData('password-user-'.A_USER_CHAT_ID,$text);
			sendEmailChangePassword(A_USER_CHAT_ID, $text);
			
		    $sendMessage->chat_id = A_USER_CHAT_ID;
		    $sendMessage->text = "Chúng tôi vừa gửi code xác nhận thay đổi Password của bạn, vui lòng nhập code dưới đây\nLưu ý: Code có hiệu lực trong vòng 5 phút";
		    //removeData('email-'.A_USER_CHAT_ID);
		    setData('change-password-step-'.A_USER_CHAT_ID,'2');
		  break;
		case '2':
			$passwordUser 		=		trim(getData('password-user-'.A_USER_CHAT_ID));
			if(checkConfirmPassword(A_USER_CHAT_ID, $text) == true) {
				updateUserPassword(A_USER_CHAT_ID, trim($passwordUser));
				$sendMessage->chat_id = A_USER_CHAT_ID;
		    	$sendMessage->text = "Cập nhật Password thành công !";
				removeData('password-user-'.A_USER_CHAT_ID);
				setData('change-password-step-'.A_USER_CHAT_ID,'0');
			} else {
				$sendMessage->chat_id = A_USER_CHAT_ID;
		    	$sendMessage->text = "Yêu cầu của bạn đã bị hủy do nhập sai code hoặc quá thời gian quy định, vui lòng thao tác lại!";
				removeData('password-user-'.A_USER_CHAT_ID);
				setData('change-password-step-'.A_USER_CHAT_ID,'0');
			}
		  break;
		default:

		  break;
	} // End Change Facebook

	switch ($stepExchange) {
		case '1':
		  $currentUser      =     getCurrentUser(A_USER_CHAT_ID);
		  if(trim($currentUser) == strtolower(trim($text))) {
		    $sendMessage->chat_id   =   A_USER_CHAT_ID;
		    $sendMessage->text      =   'Bạn không được chuyển cho chính user của bạn, vui lòng nhập user khác !';
		    setData('step-exchange-'.A_USER_CHAT_ID,'1');
		  } else if(checkUserExchange($text) == true) {
		    $sendMessage->chat_id   =   A_USER_CHAT_ID;
		    $sendMessage->text      =   checkCoinUser(A_USER_CHAT_ID);
		    setData('user-exchange-'.A_USER_CHAT_ID,$text);
		    setData('step-exchange-'.A_USER_CHAT_ID,'2');
		  }  else {
		    $sendMessage->chat_id   =   A_USER_CHAT_ID;
		    $sendMessage->text      =   'Username này chưa đăng ký, vui lòng nhập lại username:';
		    setData('step-exchange-'.A_USER_CHAT_ID,'1');
		  }
		  break;
		case '2':
		    if(checkUserChoosePlan(A_USER_CHAT_ID, $text) == true) {
		      setData('plan-exchange-'.A_USER_CHAT_ID,$text);
		      $sendMessage->chat_id   =   A_USER_CHAT_ID;
		      $sendMessage->text      =   'Nhập số coin muốn chuyển: ';
		      /*$sendMessage->text      =   "Vui lòng xác nhận lại các thông tin sau:\n Username chuyển coin: ". $userExchange ." (".getFullName($userExchange) ." )\n Số Coin muốn chuyển: ";*/
		      setData('step-exchange-'.A_USER_CHAT_ID,'3');
		    } else {
		      $sendMessage->chat_id   =   A_USER_CHAT_ID;
		      $sendMessage->text      =   'Bạn nhập tên Plan chưa đúng, vui lòng nhập lại các plan bạn đã tham gia: ';
		      setData('step-exchange-'.A_USER_CHAT_ID,'2');
		    }
		  break;
		case '3':
		    if(is_numeric($text)) {
		      setData('coin-exchange-'.A_USER_CHAT_ID,$text);
		      $userExchange           =   strtolower(getData('user-exchange-'.A_USER_CHAT_ID));
		      $planExchange           =   getData('plan-exchange-'.A_USER_CHAT_ID);
		      $coinExchange           =   getData('coin-exchange-'.A_USER_CHAT_ID);
		      if(checkEnoughCoinTransfer(A_USER_CHAT_ID, $planExchange, $coinExchange)) {
		        /*$sendMessage->chat_id   =   A_USER_CHAT_ID;
		        $coinWithFee            =   $coinExchange - ($coinExchange * COIN_FEE);
		        $sendMessage->text      =   "Vui lòng xác nhận lại các thông tin sau:\nUsername chuyển coin: ". $userExchange ." (".getFullName($userExchange) .")\nSố Coin muốn chuyển: ".$coinWithFee ." ".strtoupper($planExchange) ."\n(Đã trừ fee ".(COIN_FEE*100)."%)";*/
		        $userExchange           =   strtolower(getData('user-exchange-'.A_USER_CHAT_ID));
		        $planExchange           =   strtoupper(getData('plan-exchange-'.A_USER_CHAT_ID));
		        $coinExchange           =   getData('coin-exchange-'.A_USER_CHAT_ID);
		        $coinWithFee            =   $coinExchange - ($coinExchange * COIN_FEE);
		        $getUserRequest         =   getCurrentUser(A_USER_CHAT_ID);
		        $emailUserSend          =   strtolower(getUserEmail($getUserRequest));
		        $coinFee                =   COIN_FEE*100;
		        $tokenCode 				=	createRandomToken();
		        setData('token-exchange-'.A_USER_CHAT_ID,$tokenCode);
		        sendConfirmExchange(A_USER_CHAT_ID, $emailUserSend, $userExchange, $coinExchange, $planExchange, $tokenCode);
		        $sendMessage->chat_id   =   A_USER_CHAT_ID;
		        $sendMessage->text      =   "Chúng tôi đã gửi code xác nhận giao dịch của bạn qua email, vui lòng nhập code dưới đây: (Lưu ý: Code có hiệu lực trong vòng 5 phút) ";
		        setData('step-exchange-'.A_USER_CHAT_ID,'4');
		      } else {
		        $sendMessage->chat_id   =   A_USER_CHAT_ID;
		        $sendMessage->text      =   'Số Coin bạn đang có không đủ, vui lòng nhập lại số coin khác:';
		        setData('step-exchange-'.A_USER_CHAT_ID,'3');
		      }
		    } else {
		      $sendMessage->chat_id   =   A_USER_CHAT_ID;
		      $sendMessage->text      =   'Vui lòng nhập số coin chỉ là số (không nhập text) !';
		      setData('step-exchange-'.A_USER_CHAT_ID,'3');
		    }
		  break;
		case '4':
		    $getUserRequest         =   getCurrentUser(A_USER_CHAT_ID);
		    $userExchange           =   strtolower(getData('user-exchange-'.A_USER_CHAT_ID));
		    $tokenCode 				=	getData('token-exchange-'.A_USER_CHAT_ID);
		    if(checkConfirmCode($getUserRequest, $userExchange, $tokenCode, $text) == true) {
		      $coinExchange           =   getData('coin-exchange-'.A_USER_CHAT_ID);
		      $coinWithFee            =   $coinExchange - ($coinExchange * COIN_FEE);
		      $planExchange           =   getData('plan-exchange-'.A_USER_CHAT_ID);
		      $coinFee                =   COIN_FEE * 100;
		      $adminFee               =   $coinExchange - $coinWithFee;
		      $idTransaction 		     =   updateStatusTransactions($getUserRequest, $userExchange, $text, $adminFee, $planExchange);
		      transferUserCoin($getUserRequest, $userExchange, $coinExchange, $planExchange, COIN_FEE);
		      
		      $sendMessage->chat_id   =   A_USER_CHAT_ID;
		      $sendMessage->text      =   'Giao dịch chuyển coin thành công, vui lòng kiểm tra email để xem thông tin !';
		      if(!empty($idTransaction)) {
		      	if(checkUserRoles($userExchange) == 'member') {
		      		    $today            =       date("d/m/Y");
				    	        $adminFee     	  =   	  (double)$adminFee;
				    	        updateAdminFee($today, $adminFee, $planExchange, $idTransaction);
				         }
		      }
		        removeData('user-exchange-'.A_USER_CHAT_ID);
		        removeData('plan-exchange-'.A_USER_CHAT_ID);
		        removeData('coin-exchange-'.A_USER_CHAT_ID);
		        removeData('token-exchange-'.A_USER_CHAT_ID);
		        setData('step-exchange-'.A_USER_CHAT_ID,'0');
		      
		    } else {
		      $sendMessage->chat_id   =   A_USER_CHAT_ID;
		      $sendMessage->text      =   'Yêu cầu Chuyển Coin của bạn đã bị hủy do nhập sai code hoặc giao dịch đã hết hạn, vui lòng thao tác lại !';
		      removeData('user-exchange-'.A_USER_CHAT_ID);
		      removeData('plan-exchange-'.A_USER_CHAT_ID);
		      removeData('coin-exchange-'.A_USER_CHAT_ID);
		      removeData('token-exchange-'.A_USER_CHAT_ID);
		      setData('step-exchange-'.A_USER_CHAT_ID,'0');
		    }
		    setData('step-exchange-'.A_USER_CHAT_ID,'0');
		  break;
		default:
		  # code...
		  break;
	}

	switch ($stepAddCoin) {
		case '3':
			//setData('plan-add-coin-'.A_USER_CHAT_ID,$text);
			if(!is_numeric($text)) {
				$sendMessage->chat_id 	= 	A_USER_CHAT_ID;
				$sendMessage->text 		= 	'Bạn nhập số Coin không đúng, vui lòng nhập lại !';
				setData('step-add-coin-'.A_USER_CHAT_ID,'3');
			} else {
				$sendMessage->chat_id = A_USER_CHAT_ID;
				$sendMessage->text = 'Vui lòng nhập chuỗi TxtId sau khi bạn đã chuyển Coin:';
				setData('coin-add-coin-'.A_USER_CHAT_ID,$text);
				setData('step-add-coin-'.A_USER_CHAT_ID,'4');
			}
			break;
		case '4':
			setData('txtid-add-coin-'.A_USER_CHAT_ID, $text);
			$currentUser 				=	getCurrentUser(A_USER_CHAT_ID);
			$planAdd 					=	getData('plan-add-coin-'.A_USER_CHAT_ID);
			$coinAdd 					=	getData('coin-add-coin-'.A_USER_CHAT_ID);
			$txtIdAdd 					=	trim(getData('txtid-add-coin-'.A_USER_CHAT_ID));
			$kyhieuCoin                 =   getKyHieuCoin($planAdd);
			$arrayInlineKeyBoard 		=	[
											    'inline_keyboard' => [
											        [
											            ['text' => '✅ Xác Nhận', 'callback_data' => 'confirm-add_yes'],
											            ['text' => '❌ Hủy', 'callback_data' => 'confirm-add_no'],
											        ],
											    ]
											];	
			$inlineKeyboard 			= 	new Markup($arrayInlineKeyBoard);
			$sendMessage->chat_id 		= 	A_USER_CHAT_ID;
			$sendMessage->text 			= 	"Vui lòng xác nhận những thông tin sau:\nUsername: ".$currentUser."\nSố Coin yêu cầu thêm: ".$coinAdd. " ".strtoupper($kyhieuCoin)."\nTxtid: ".$txtIdAdd."\n Chọn yêu cầu dưới đây:";
			$sendMessage->disable_web_page_preview = true;
			$sendMessage->parse_mode 	= 	'Markdown';
			$sendMessage->reply_markup 	= 	$inlineKeyboard;
			break;
		default:
			# code...
			break;
	} // Step ADD Coin
?>