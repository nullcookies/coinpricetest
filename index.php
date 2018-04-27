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

$step           =   getData('step-'.A_USER_CHAT_ID);
$verified       =   setData('verified','no');

  switch ($text) {
    case '/start':
      setData('step-'.A_USER_CHAT_ID,'1');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Username của bạn:';
      break;
    case '/huy':
      setData('step-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Thông tin đã hủy ! Vui lòng nhấn /start để đăng nhập lại';
      break;
    case '/dangky':
      
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
            setData('verified','yes');
          } else {
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Đăng nhập không thành công ! Vui lòng nhấn /start để đăng nhập lại';
            setData('step-'.A_USER_CHAT_ID,'0');
            setData('verified','no');
          }
          break;
        default:
            $sendMessage->chat_id = A_USER_CHAT_ID;
            $sendMessage->text = 'Vui lòng nhấn /start để đăng nhập';
          break;
      }
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
          $arrayInlineKeyBoard    					  =   array();
          $plansArray             					  =   checkDetailPlan($queryUserId);
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

          $inlineKeyboard               			  = 	new Markup($arrayInlineKeyBoard);
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