<?php

use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
$sendMessage->chat_id = A_USER_CHAT_ID;
date_default_timezone_set('Asia/Ho_Chi_Minh');
$date = new DateTime();
if($date->format('D') === 'Fri')  {
	$sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
} else {
	$sendMessage->text = 'Chọn plan bạn muốn yêu cầu:';
	$row = null;
	$arrayInlineKeyBoard    =   array();
	$plansArray             =   checkDetailPlan(A_USER_CHAT_ID);
	foreach($plansArray as $key => $value) {
	    $buttonText         =         ucfirst($value['ten_plan']) . ' - Trạng Thái: '. ucfirst($value['tai_dau_tu']) . ' Tái';
	    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   $buttonText;
	    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'request_'.$value['ten_plan'];
	}

	$inlineKeyboard = new Markup($arrayInlineKeyBoard);

	$sendMessage->disable_web_page_preview = true;
	$sendMessage->parse_mode = 'Markdown';
	$sendMessage->reply_markup = $inlineKeyboard;
}