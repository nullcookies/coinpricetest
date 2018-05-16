<?php
declare(strict_types = 1);

include __DIR__.'/basics.php';
include __DIR__.'/functions.php';

use React\EventLoop\Factory;
use unreal4u\TelegramAPI\HttpClientRequestHandler;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\AnswerCallbackQuery;
use unreal4u\TelegramAPI\Telegram\Methods\EditMessageText;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\User;
use unreal4u\TelegramAPI\TgLog;

$loop 			= 	Factory::create();
$tgLog 			= 	new TgLog(BOT_TOKEN, new HttpClientRequestHandler($loop));

$sendMessage 	= 	new SendMessage();

$step           	      =   getData('step-'.A_USER_CHAT_ID);
$stepEmail              =   getData('change-email-step-'.A_USER_CHAT_ID);
$stepFullname           =   getData('change-fullname-step-'.A_USER_CHAT_ID);
$stepFacebook           =   getData('change-facebook-step-'.A_USER_CHAT_ID);
$stepWallet             =   getData('change-wallet-step-'.A_USER_CHAT_ID);
$stepRegister           =   getData('step-register-'.A_USER_CHAT_ID);
//$verified            =   setData('verified-'.A_USER_CHAT_ID,'no');

  switch ($text) {
    case '/start':
      setData('step-'.A_USER_CHAT_ID,'0');
      setData('step-register-'.A_USER_CHAT_ID,'0');
      require_once __DIR__.'/types/nut_khoi_tao.php';
      break;
    case '/huy':
      setData('step-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Thông tin đã hủy ! Vui lòng nhấn /start để đăng nhập lại';
      break;
    case $nutKhoiTao[0]: // Đăng Nhập
      setData('step-'.A_USER_CHAT_ID,'1');
      setData('step-register-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Username của bạn:';  
      break;
    case $nutKhoiTao[1]: // Đăng Ký
      setData('step-register-'.A_USER_CHAT_ID,'1');
      setData('step-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Username bạn muốn đăng ký:'; 
      //require_once __DIR__.'/request/register.php';
      break;
    case $nutYeuCau[0]:
      require_once __DIR__.'/types/inline_keyboard_plans.php';
      break;
    case $nutYeuCau[1]:
      require_once __DIR__.'/types/yeu_cau_tuan.php';
      break;
    case $nutYeuCau[2]:
      require_once __DIR__.'/types/yeu_cau_thang.php';
      break;
    case $nutYeuCau[3]:
      require_once __DIR__.'/types/chuyen_coin.php';
      break;
    case $nutYeuCau[4]:
      require_once __DIR__.'/types/sua_thong_tin.php';
      break;
    case $nutChinhSua[0]: // Sửa Số Ví
      //require_once __DIR__.'/types/wallet.php';
      setData('change-wallet-step-'.A_USER_CHAT_ID,'1');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Plan bạn muốn thay đổi số ví';
      break;
    case $nutChinhSua[1]: // Sửa Email
      setData('change-email-step-'.A_USER_CHAT_ID,'1');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Email bạn muốn thay đổi:';
      //require_once __DIR__.'/settings/email.php';
      
      break;
    case $nutChinhSua[2]: // Sửa Họ Tên
      setData('change-fullname-step-'.A_USER_CHAT_ID,'1');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Họ Tên bạn muốn thay đổi:';
      break;
    case $nutChinhSua[3]: // Sửa Facebook
      setData('change-facebook-step-'.A_USER_CHAT_ID,'1');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Facebook bạn muốn thay đổi:';
      break;
    case $nutChinhSua[4]:
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = getCurrentUserInfo(A_USER_CHAT_ID);
      break;
    case $nutChinhSua[5]: // Quay Lại
      require_once __DIR__.'/types/init_keyboards.php';
      break;
    default:
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
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = "Chọn một trong số các plan sau đây bạn muốn tham gia: \n".showAllPlans();
            setData('step-register-'.A_USER_CHAT_ID,'6');
          } else {
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Email bạn nhập không đúng, vui lòng nhập lại !';
            setData('step-register-'.A_USER_CHAT_ID,'5');
          }
          break;
        case '6':
          if($text != '') {
            setData('plan-register-'.A_USER_CHAT_ID,$text);
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Nhập số ví của Plan: ';
            setData('step-register-'.A_USER_CHAT_ID,'7');
          } else {
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Vui lòng không để trống số ví !';
            setData('step-register-'.A_USER_CHAT_ID,'6');
          }
          break;
        case '7':
            setData('wallet-register-'.A_USER_CHAT_ID,$text);
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Nhập yêu cầu khác (nếu có):';
            setData('step-register-'.A_USER_CHAT_ID,'8');
          break;
        case '8':
            setData('other-register-'.A_USER_CHAT_ID,$text);
            $registerUser         =   vn_to_str(getData('username-register-'.A_USER_CHAT_ID));
            $registerPassword     =   getData('password-register-'.A_USER_CHAT_ID);
            $registerFullname     =   getData('fullname-register-'.A_USER_CHAT_ID);
            $registerFacebook     =   getData('facebook-register-'.A_USER_CHAT_ID);
            $registerEmail        =   getData('email-register-'.A_USER_CHAT_ID);
            $registerPlan         =   getData('plan-register-'.A_USER_CHAT_ID);
            $registerWallet       =   getData('wallet-register-'.A_USER_CHAT_ID);
            $registerOther        =   getData('other-register-'.A_USER_CHAT_ID);
            $sendMessage->chat_id =   A_USER_CHAT_ID;
            $sendMessage->text    =   "Vui lòng xác nhận những thông tin bạn đã đăng ký dưới đây:\nUsername: ".$registerUser."\nPassword: ".$registerPassword."\nHọ Tên: ".strtolower($registerFullname)."\nFacebook: ".$registerFacebook."\nEmail: ".$registerEmail."\nPlan Đăng Ký: ".$registerPlan."\nSố ví: ".$registerWallet."\nYêu Cầu Khác: ".$registerOther."\nVui lòng chọn 'yes' để xác nhận hoặc 'no' để hủy thông tin và nhập lại";
            setData('step-register-'.A_USER_CHAT_ID,'9');  
          break;
        case '9':
          switch ($text) {
            case 'yes':
              $resultText   =   '';
              $registerUser         =   vn_to_str(getData('username-register-'.A_USER_CHAT_ID));
              $registerPassword     =   getData('password-register-'.A_USER_CHAT_ID);
              $registerFullname     =   getData('fullname-register-'.A_USER_CHAT_ID);
              $registerFacebook     =   getData('facebook-register-'.A_USER_CHAT_ID);
              $registerEmail        =   getData('email-register-'.A_USER_CHAT_ID);
              $registerPlan         =   getData('plan-register-'.A_USER_CHAT_ID);
              $registerWallet       =   getData('wallet-register-'.A_USER_CHAT_ID);
              $registerOther        =   getData('other-register-'.A_USER_CHAT_ID);
              $sendMessage->chat_id =   A_USER_CHAT_ID;
              $result   =   sendRegisterMail(strtolower($registerFullname), $registerPassword, $registerFullname, $registerFacebook, $registerEmail, $registerPlan, $registerWallet, $registerOther);
              if($result  ==   true) {
                $resultText   =   'Đăng ký thành công, chúng tôi sẽ liên hệ bạn trong thời gian sớm nhất. Xin cám ơn !';
              }
              $sendMessage->text    =   $resultText;
              setData('step-register-'.A_USER_CHAT_ID,'0');
              removeData('username-register-'.A_USER_CHAT_ID);
              removeData('password-register-'.A_USER_CHAT_ID);
              removeData('fullname-register-'.A_USER_CHAT_ID);
              removeData('facebook-register-'.A_USER_CHAT_ID);
              removeData('email-register-'.A_USER_CHAT_ID);
              removeData('plan-register-'.A_USER_CHAT_ID);
              removeData('wallet-register-'.A_USER_CHAT_ID);
              removeData('other-register-'.A_USER_CHAT_ID);
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
              removeData('plan-register-'.A_USER_CHAT_ID);
              removeData('wallet-register-'.A_USER_CHAT_ID);
              removeData('other-register-'.A_USER_CHAT_ID);
              break;
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

      break;
  }

$promise = $tgLog->performApiRequest($sendMessage);

// Kiểm Tra Query
if(!empty($queryData)) {
$arrayQueryData     =   explode('_', $queryData);
$getQueryType       =   $arrayQueryData[0];
  switch ($getQueryType) {
    case 'print':
      $answerQueryText                            	  =     answerPlanDetail($queryUserId, $queryData);
      $answerCallbackQuery                        	  =     new AnswerCallbackQuery();
      $answerCallbackQuery->callback_query_id     	  =     $queryid;
      $answerCallbackQuery->show_alert            	  =     true;
      $answerCallbackQuery->text                  	  =     $answerQueryText;
      $messageCorrectionPromise                   	  =     $tgLog->performApiRequest($answerCallbackQuery);
      break;
    case 'request':
      $editMessageText                            	  =     new EditMessageText();
      $editMessageText->chat_id                   	  =     $queryUserId;
      $editMessageText->message_id                	  =     $querymsgId;
      $editMessageText->text                      	  =     "Vui lòng chọn yêu cầu cho Plan ".strtoupper($arrayQueryData[1]);
      $inlineKeyboard = new Markup([
          'inline_keyboard' => [
              [
                  ['text' => '✅ Có', 'callback_data' => 'week_'.$arrayQueryData[1].'_yes'],
                  ['text' => '❌ Không', 'callback_data' => 'week_'.$arrayQueryData[1].'_no'],
              ],
              [
                  ['text' => '🔙 Quay Lại', 'callback_data' => 'back_week'],
              ],
          ]
      ]);
      $editMessageText->reply_markup              	  =     $inlineKeyboard;

      $messageCorrectionPromise                   	  =     $tgLog->performApiRequest($editMessageText);
      break; // End yêu cầu rút tuần
    case 'request-month':
      $editMessageText                            	  =     new EditMessageText();
      $editMessageText->chat_id                   	  =     $queryUserId;
      $editMessageText->message_id                	  =     $querymsgId;
      $editMessageText->text                      	  =     "Vui lòng chọn yêu cầu cho Plan ".strtoupper($arrayQueryData[1]);
      $inlineKeyboard = new Markup([
          'inline_keyboard' => [
              [
                  ['text' => '💸 Rút Lãi', 'callback_data' => 'month_'.$arrayQueryData[1].'_rut-lai'],
                  ['text' => '💰 Rút Gốc', 'callback_data' => 'month_'.$arrayQueryData[1].'_rut-goc'],
                  ['text' => '❌ Hủy Yêu Cầu', 'callback_data' => 'month_'.$arrayQueryData[1].'_huy'],
              ],
              [
                  ['text' => '🔙 Quay Lại', 'callback_data' => 'back_month'],
              ],
          ]
      ]);
      $editMessageText->reply_markup              	  =     $inlineKeyboard;

      $messageCorrectionPromise                   	  =     $tgLog->performApiRequest($editMessageText);
      break; // End yêu cầu rút tháng
    case 'week':
      switch ($arrayQueryData[2]) {
        case 'yes':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'có', 'week');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
        
        case 'no':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'không', 'week');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
      }
      break; // End Yêu Cầu Tái Rút Tuần
    case 'month':
      switch ($arrayQueryData[2]) {
        case 'rut-lai':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'Rút Lãi', 'month');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
        
        case 'rut-goc':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'Rút Gốc', 'month');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
        case 'huy':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'Chưa có yêu cầu', 'month');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
      }
      break; // End Yêu Cầu Tái Rút Tháng
    case 'back':
      switch ($arrayQueryData[1]) {
        case 'week':
          $editMessageText                            =     new EditMessageText();
          $editMessageText->chat_id                   =     $queryUserId;
          $editMessageText->message_id                =     $querymsgId;
          $editMessageText->text                      =     "Chọn plan bạn muốn yêu cầu:";
          $arrayInlineKeyBoard    					  =   	array();
          $plansArray             					  =   	checkDetailPlan($queryUserId);
          foreach($plansArray as $key => $value) {
              $buttonText         					  =         ucfirst($value['ten_plan']) . ' - Trạng Thái: '. ucfirst($value['tai_dau_tu']) . ' Tái';
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   $buttonText;
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'request_'.$value['ten_plan'];
          }

          $inlineKeyboard               			  = new Markup($arrayInlineKeyBoard);
          $editMessageText->reply_markup              =     $inlineKeyboard;

          $messageCorrectionPromise                   =     $tgLog->performApiRequest($editMessageText);
          break; // Nút Back Tuần
        case 'month':
          $editMessageText                            =     new EditMessageText();
          $editMessageText->chat_id                   =     $queryUserId;
          $editMessageText->message_id                =     $querymsgId;
          $editMessageText->text                      =     "Chọn plan bạn muốn rút Coin: (rút lãi hoặc gốc theo tháng)";
          $arrayInlineKeyBoard    					  =   	array();
          $plansArray             					  =   	checkDetailPlan($queryUserId);
          foreach($plansArray as $key => $value) {
          	  if(empty($value['yeu_cau_khac'])) {
				$value['yeu_cau_khac'] 	=	"Chưa có yêu cầu";
			  }
              $buttonText         					  =         ucfirst($value['ten_plan']) . ' - Trạng Thái: '. ucfirst($value['yeu_cau_khac']);
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   $buttonText;
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'request-month_'.$value['ten_plan'];
          }

          $inlineKeyboard               			        = 	new Markup($arrayInlineKeyBoard);
          $editMessageText->reply_markup              =     $inlineKeyboard;

          $messageCorrectionPromise                   =     $tgLog->performApiRequest($editMessageText);
          break;
        default:
          # code...
          break;
      }
      break; // End Back Button
    default:
      # code...
      break;
  }
}
$loop->run();