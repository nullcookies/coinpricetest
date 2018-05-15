<?php
$stepEmail           =   getData('change-email-step-'.A_USER_CHAT_ID);
switch ($stepEmail) {
	case '1':
		$sendMessage->chat_id = A_USER_CHAT_ID;
      	$sendMessage->text = 'Vui lòng nhập Email bạn muốn thay đổi:';
      	setData('change-email-step-'.A_USER_CHAT_ID,'2');
		break;
	case '2':
		$userEmail 	=	setData('email-'.A_USER_CHAT_ID,$text);
		if (filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
			$sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'OK';
            removeData('email-'.A_USER_CHAT_ID);
            setData('change-email-step-'.A_USER_CHAT_ID,'1');
		} else {
			$sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Email bạn nhập không đúng, vui lòng nhập lại...';
			removeData('email-'.A_USER_CHAT_ID);
			setData('change-email-step-'.A_USER_CHAT_ID,'1');
		}
		break;
	default:
		# code...
		break;
}