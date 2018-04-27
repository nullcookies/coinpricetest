<?php

use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
$sendMessage->chat_id = A_USER_CHAT_ID;
date_default_timezone_set('Asia/Ho_Chi_Minh');
$date = new DateTime();

$sendMessage->text = 'Chọn plan bạn muốn rút Coin: (rút lãi hoặc gốc theo tháng)';
$row = null;
$arrayInlineKeyBoard    =   array();
$plansArray             =   checkDetailPlan(A_USER_CHAT_ID);
foreach($plansArray as $key => $value) {
	if(empty($value['yeu_cau_khac'])) {
		$value['yeu_cau_khac'] 	=	"Chưa có yêu cầu";
	}
    $buttonText         =         ucfirst($value['ten_plan']) . ' - Trạng Thái: '. ucfirst($value['yeu_cau_khac']);
    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   $buttonText;
    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'request-month_'.$value['ten_plan'];
}

$inlineKeyboard = new Markup($arrayInlineKeyBoard);

$sendMessage->disable_web_page_preview = true;
$sendMessage->parse_mode = 'Markdown';
$sendMessage->reply_markup = $inlineKeyboard;
