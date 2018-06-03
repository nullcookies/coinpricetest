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

$loop       =   Factory::create();
$tgLog      =   new TgLog(BOT_TOKEN, new HttpClientRequestHandler($loop));

$sendMessage  =   new SendMessage();

$step                   =   getData('step-'.A_USER_CHAT_ID);
$stepEmail              =   getData('change-email-step-'.A_USER_CHAT_ID);
$stepFullname           =   getData('change-fullname-step-'.A_USER_CHAT_ID);
$stepFacebook           =   getData('change-facebook-step-'.A_USER_CHAT_ID);
$stepWallet             =   getData('change-wallet-step-'.A_USER_CHAT_ID);
$stepRegister           =   getData('step-register-'.A_USER_CHAT_ID);
$stepExchange           =   getData('step-exchange-'.A_USER_CHAT_ID);
$stepAddCoin            =   getData('step-add-coin-'.A_USER_CHAT_ID);
$stepPassword           =   getData('change-password-step-'.A_USER_CHAT_ID);
//$verified            =   setData('verified-'.A_USER_CHAT_ID,'no');
$tokenCode 				      =		getData('token-exchange-'.A_USER_CHAT_ID);

  switch ($text) {
    case '/start':
      setData('step-'.A_USER_CHAT_ID,'0');
      setData('step-register-'.A_USER_CHAT_ID,'0');
      setData('change-wallet-step-'.A_USER_CHAT_ID,'0');
      setData('change-facebook-step-'.A_USER_CHAT_ID, '0');
      setData('change-fullname-step-'.A_USER_CHAT_ID,'0');
      setData('change-email-step-'.A_USER_CHAT_ID, '0');
      setData('step-exchange-'.A_USER_CHAT_ID, '0');
      setData('step-add-coin'.A_USER_CHAT_ID, '0');
      setData('change-password-step-'.A_USER_CHAT_ID,'0');
      require_once __DIR__.'/types/nut_khoi_tao.php';
      break;
    case '/huy':
      setData('step-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Thông tin đã hủy ! Vui lòng nhấn /start để đăng nhập lại';
      break;
    case '/clear':
      setData('step-'.A_USER_CHAT_ID,'0');
      setData('step-register-'.A_USER_CHAT_ID,'0');
      setData('change-wallet-step-'.A_USER_CHAT_ID,'0');
      setData('change-facebook-step-'.A_USER_CHAT_ID, '0');
      setData('change-fullname-step-'.A_USER_CHAT_ID,'0');
      setData('change-email-step-'.A_USER_CHAT_ID, '0');
      setData('step-exchange-'.A_USER_CHAT_ID, '0');
      setData('step-add-coin'.A_USER_CHAT_ID, '0');
      setData('change-password-step-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Cache is removed successfully !';
      break;
    case $nutKhoiTao[0]: // Đăng Nhập
      clearCache();
      setData('step-'.A_USER_CHAT_ID,'1');
      setData('step-register-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = 'Vui lòng nhập Username của bạn:';  
      break;
    case $nutKhoiTao[1]: // Đăng Ký
      clearCache();
      setData('step-'.A_USER_CHAT_ID,'0');
      $sendMessage->chat_id = A_USER_CHAT_ID;
      if(checkTelegramExisting(A_USER_CHAT_ID)) {
        setData('step-register-'.A_USER_CHAT_ID,'0');
        $sendMessage->text = 'ID Telegram của bạn đã được đăng ký, mọi thắc mắc xin gửi mail về ta.team.rb@gmail.com'; 
      } else {
        setData('step-register-'.A_USER_CHAT_ID,'1');
        $sendMessage->text = 'Vui lòng nhập Username bạn muốn đăng ký:'; 
      }
      //require_once __DIR__.'/request/register.php';
      break;
    case $nutYeuCau[0]:
      clearCache();
      updateFailed($tokenCode);
      require_once __DIR__.'/types/inline_keyboard_plans.php';
      break;
    case $nutYeuCau[1]:
      clearCache();
      updateFailed($tokenCode);
      require_once __DIR__.'/types/yeu_cau_tuan.php';
      break;
    case $nutYeuCau[2]:
      clearCache();
      updateFailed($tokenCode);
      require_once __DIR__.'/types/yeu_cau_thang.php';
      break;
    case $nutYeuCau[3]:
      clearCache();
      if(checkNgayChiaLai() == true) {
      	setData('step-exchange-'.A_USER_CHAT_ID,'0');
      	$sendMessage->chat_id = A_USER_CHAT_ID;
	    $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
      	if(checkUserHaveEmail(A_USER_CHAT_ID) == true) {
	        setData('step-exchange-'.A_USER_CHAT_ID,'1');
	        updateFailed($tokenCode);
	        $sendMessage->chat_id = A_USER_CHAT_ID;
	        $sendMessage->text = 'Vui lòng nhập tên username bạn muốn chuyển: ';
	      } else {
	        setData('step-exchange-'.A_USER_CHAT_ID,'0');
	        $sendMessage->chat_id = A_USER_CHAT_ID;
	        $sendMessage->text = 'Bạn chưa đăng ký email, vui lòng vào phần Sửa Thông Tin để đăng ký email !';
	      }
      }
      //require_once __DIR__.'/types/chuyen_coin.php';
      break;
    case $nutYeuCau[4]: // Sửa Thông Tin
      clearCache();
      updateFailed($tokenCode);
      require_once __DIR__.'/types/sua_thong_tin.php';
      break;
    case $nutYeuCau[5]:
      clearCache();
      updateFailed($tokenCode);
      if(checkNgayChiaLai() == true) {
        setData('step-add-coin-'.A_USER_CHAT_ID,'0');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
        setData('step-add-coin-'.A_USER_CHAT_ID,'1');
        require_once __DIR__.'/types/dang_ky_coin.php';
      }
      break;
    case $nutChinhSua[0]: // Đổi Password
      clearCache();
      if(checkNgayChiaLai() == true) {
        setData('change-password-step-'.A_USER_CHAT_ID,'0');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
        if(checkUserHaveEmail(A_USER_CHAT_ID) == true) {
          setData('change-password-step-'.A_USER_CHAT_ID,'1');
          $sendMessage->chat_id = A_USER_CHAT_ID;
          $sendMessage->text = "Vui lòng nhập Password bạn muốn đổi:\nLưu ý: Nhập Password bảo mật để tránh những mất mát sau này";
        } else {
          setData('change-password-step-'.A_USER_CHAT_ID,'0');
          $sendMessage->chat_id = A_USER_CHAT_ID;
          $sendMessage->text = 'Bạn chưa đăng ký email, vui lòng vào phần Sửa Thông Tin để đăng ký email !';
        }
      }
      break;
    case $nutChinhSua[1]: // Sửa Số Ví
      clearCache();
      //require_once __DIR__.'/types/wallet.php';
      if(checkNgayChiaLai() == true) {
      	setData('change-wallet-step-'.A_USER_CHAT_ID,'0');
      	$sendMessage->chat_id = A_USER_CHAT_ID;
	      $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
      	if(checkStatusWallet() == true) {
	        setData('change-wallet-step-'.A_USER_CHAT_ID,'1');
	        $sendMessage->chat_id = A_USER_CHAT_ID;
	        $sendMessage->text = 'Vui lòng nhập Plan bạn muốn thay đổi số ví';
	      } else {
	        $sendMessage->chat_id = A_USER_CHAT_ID;
	        $sendMessage->text = 'Chức năng này bị khóa tạm thời bởi người quản trị, vui lòng update lần sau.';
	      }
      }
      break;  
    case $nutChinhSua[2]: // Sửa Email
      clearCache();
      if(checkNgayChiaLai() == true) {
        setData('change-email-step-'.A_USER_CHAT_ID,'0');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
        setData('change-email-step-'.A_USER_CHAT_ID,'1');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Vui lòng nhập Email bạn muốn thay đổi:';
      }
      break;
    case $nutChinhSua[3]: // Sửa Họ Tên
      clearCache();
      if(checkNgayChiaLai() == true) {
        setData('change-fullname-step-'.A_USER_CHAT_ID,'0');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
        setData('change-fullname-step-'.A_USER_CHAT_ID,'1');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Vui lòng nhập Họ Tên bạn muốn thay đổi:';
      }
      break;
    case $nutChinhSua[4]: // Sửa Facebook
      clearCache();
      if(checkNgayChiaLai() == true) {
        setData('change-facebook-step-'.A_USER_CHAT_ID,'0');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
        setData('change-facebook-step-'.A_USER_CHAT_ID,'1');
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Vui lòng nhập Facebook bạn muốn thay đổi:';
      }
      break;
    case $nutChinhSua[5]: // Xem thông tin
      clearCache();
      $sendMessage->chat_id = A_USER_CHAT_ID;
      $sendMessage->text = getCurrentUserInfo(A_USER_CHAT_ID);
      break;
    case $nutChinhSua[6]: // Quay Lại
      clearCache();
      require_once __DIR__.'/types/init_keyboards.php';
      break;
    default:
      if(checkNgayChiaLai() == true) {
        $sendMessage->chat_id = A_USER_CHAT_ID;
        $sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
      } else {
        require_once __DIR__.'/default_step.php';
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
      $answerQueryText                                =     answerPlanDetail($queryUserId, $queryData);
      $answerCallbackQuery                            =     new AnswerCallbackQuery();
      $answerCallbackQuery->callback_query_id         =     $queryid;
      $answerCallbackQuery->show_alert                =     true;
      $answerCallbackQuery->text                      =     $answerQueryText;
      $messageCorrectionPromise                       =     $tgLog->performApiRequest($answerCallbackQuery);
      break;
    case 'request':
      $editMessageText                                =     new EditMessageText();
      $editMessageText->chat_id                       =     $queryUserId;
      $editMessageText->message_id                    =     $querymsgId;
      $editMessageText->text                          =     "Vui lòng chọn yêu cầu cho Plan ".strtoupper($arrayQueryData[1])."\nGhi chú:\n- Chọn 'Có' để tái đầu tư\n- Chọn 'No' để rút lãi theo tuần";
      $checkDaily 									  =		 checkDailyWithdraw($arrayQueryData[1]);
      if($checkDaily  == 'daily') {
      	$inlineKeyboard = new Markup([
          'inline_keyboard' => [
	              [
	              	  ['text' => '💵 Tái Tuần', 'callback_data' => 'week_'.$arrayQueryData[1].'_check'],
	                  ['text' => '✅ Có', 'callback_data' => 'week_'.$arrayQueryData[1].'_yes'],
	                  ['text' => '❌ Không', 'callback_data' => 'week_'.$arrayQueryData[1].'_no'],
	              ],
	              [
	              	  ['text' => '💵 Tái Ngày', 'callback_data' => 'daily_'.$arrayQueryData[1].'_check'],
	              	  ['text' => '✅ Có', 'callback_data' => 'daily_'.$arrayQueryData[1].'_yes'],
	                  ['text' => '❌ Không', 'callback_data' => 'daily_'.$arrayQueryData[1].'_no'],
	              ],
	              [
	                  ['text' => '🔙 Quay Lại', 'callback_data' => 'back_week'],
	              ],
	          ]
	      ]);
      } else {
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
      }

      $editMessageText->reply_markup                  =     $inlineKeyboard;

      $messageCorrectionPromise                       =     $tgLog->performApiRequest($editMessageText);
      break; // End yêu cầu rút tuần
    case 'request-month':
      $editMessageText                                =     new EditMessageText();
      $editMessageText->chat_id                       =     $queryUserId;
      $editMessageText->message_id                    =     $querymsgId;
      $editMessageText->text                          =     "Vui lòng chọn yêu cầu cho Plan ".strtoupper($arrayQueryData[1])."\nGhi chú:\n- Chọn 'Rút Lãi' để rút lãi vào cuối tháng\n- Chọn 'Rút Gốc' để rút gốc vào cuối tháng\n- Chọn 'Hủy Yêu Cầu' để tiếp tục đầu tư";
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
      $editMessageText->reply_markup                  =     $inlineKeyboard;

      $messageCorrectionPromise                       =     $tgLog->performApiRequest($editMessageText);
      break; // End yêu cầu rút tháng
    case 'daily':
      switch ($arrayQueryData[2]) {
      	case 'check':
          $answerQueryText                            =     checkStatusDailyRequest($queryUserId, $arrayQueryData[1], 'daily');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
        case 'yes':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'có', 'daily');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
        
        case 'no':
          $answerQueryText                            =     updateRequestCoin($queryUserId, $arrayQueryData[1], 'không', 'daily');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
      }
      break; // End Yêu Cầu Tái Rút Tuần  
    case 'week':
      switch ($arrayQueryData[2]) {
      	case 'check':
          $answerQueryText                            =     checkStatusDailyRequest($queryUserId, $arrayQueryData[1], 'weekly');
          $answerCallbackQuery                        =     new AnswerCallbackQuery();
          $answerCallbackQuery->callback_query_id     =     $queryid;
          $answerCallbackQuery->show_alert            =     true;
          $answerCallbackQuery->text                  =     $answerQueryText;
          $messageCorrectionPromise                   =     $tgLog->performApiRequest($answerCallbackQuery);
          break;
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
          $arrayInlineKeyBoard                =     array();
          $plansArray                         =     checkDetailPlan($queryUserId);
          foreach($plansArray as $key => $value) {
              $buttonText                     =         ucfirst($value['ten_plan']) . ' - Trạng Thái: '. ucfirst($value['tai_dau_tu']) . ' Tái';
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   $buttonText;
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'request_'.$value['ten_plan'];
          }

          $inlineKeyboard                       = new Markup($arrayInlineKeyBoard);
          $editMessageText->reply_markup              =     $inlineKeyboard;

          $messageCorrectionPromise                   =     $tgLog->performApiRequest($editMessageText);
          break; // Nút Back Tuần
        case 'month':
          $editMessageText                            =     new EditMessageText();
          $editMessageText->chat_id                   =     $queryUserId;
          $editMessageText->message_id                =     $querymsgId;
          $editMessageText->text                      =     "Chọn plan bạn muốn rút Coin: (rút lãi hoặc gốc theo tháng)";
          $arrayInlineKeyBoard                =     array();
          $plansArray                         =     checkDetailPlan($queryUserId);
          foreach($plansArray as $key => $value) {
              if(empty($value['yeu_cau_khac'])) {
        $value['yeu_cau_khac']  = "Chưa có yêu cầu";
        }
              $buttonText                     =         ucfirst($value['ten_plan']) . ' - Trạng Thái: '. ucfirst($value['yeu_cau_khac']);
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   $buttonText;
              $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'request-month_'.$value['ten_plan'];
          }

          $inlineKeyboard                             =       new Markup($arrayInlineKeyBoard);
          $editMessageText->reply_markup              =       $inlineKeyboard;

          $messageCorrectionPromise                   =       $tgLog->performApiRequest($editMessageText);
          break;
        default:
          # code...
          break;
      }
      break; // End Back Button
    case 'add':
      $editMessageText                                =     new EditMessageText();
      $editMessageText->chat_id                       =     $queryUserId;
      $editMessageText->message_id                    =     $querymsgId;
      $editMessageText->text                          =     "Nhập số Coin bạn muốn thêm:";
      $messageCorrectionPromise                       =     $tgLog->performApiRequest($editMessageText);
      setData('plan-add-coin-'.$queryUserId,$arrayQueryData[1]);
      setData('step-add-coin-'.$queryUserId,'3');
      break; // Add thêm coin
    case 'confirm-add':
      switch ($arrayQueryData[1]) {
        case 'yes':
          $editMessageText                                =     new EditMessageText();
          $editMessageText->chat_id                       =     $queryUserId;
          $editMessageText->message_id                    =     $querymsgId;
          $editMessageText->text                          =     "Yêu cầu Thêm Coin của bạn đã được gửi thành công, chúng tôi sẽ xử lý yêu cầu của bạn trong thời gian sớm nhất, xin cám ơn !";
          $messageCorrectionPromise                       =     $tgLog->performApiRequest($editMessageText);
          $currentUser        =   getCurrentUser($queryUserId);
          $planAdd            =   getData('plan-add-coin-'.$queryUserId);
          $coinAdd            =   getData('coin-add-coin-'.$queryUserId);
          $txtIdAdd           =   trim(getData('txtid-add-coin-'.$queryUserId));
          $resultSend         =   sendEmailAddCoin($currentUser, $coinAdd, $planAdd, $txtIdAdd);
          removeData('plan-add-coin-'.$queryUserId);
          removeData('coin-add-coin-'.$queryUserId);
          removeData('txtid-add-coin-'.$queryUserId);
          setData('step-add-coin-'.$queryUserId,'0');
          break;
        case 'no':
          $editMessageText                                =     new EditMessageText();
          $editMessageText->chat_id                       =     $queryUserId;
          $editMessageText->message_id                    =     $querymsgId;
          $editMessageText->text                          =     "Yêu cầu Thêm Coin của bạn đã bị hủy !";
          $messageCorrectionPromise                       =     $tgLog->performApiRequest($editMessageText);
          removeData('plan-add-coin-'.$queryUserId);
          removeData('coin-add-coin-'.$queryUserId);
          removeData('txtid-add-coin-'.$queryUserId);
          setData('step-add-coin-'.$queryUserId,'0');
          break;
      }
      break;
    default:
      # code...
      break;
  }
}
$loop->run();