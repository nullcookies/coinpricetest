<?php

use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
$sendMessage->chat_id = A_USER_CHAT_ID;
if(checkNgayChiaLai() == true)  {
	$sendMessage->text = 'Hôm nay là ngày chia lãi, bạn vui lòng yêu cầu vào ngày khác.';
} else {
	$sendMessage->text = 'Chọn plan bạn muốn thêm Coin: ';
	$row = null;
	$arrayInlineKeyBoard    =   array();
	$plansArray             =   getCurrentPlans();
	foreach($plansArray as $key => $value) {
	    $buttonText         =         strtoupper($value['ten_plan']);
	    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   '➕ '.$buttonText;
	    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'add_'.$value['ten_plan'];
	}

	$inlineKeyboard = new Markup($arrayInlineKeyBoard);

	$sendMessage->disable_web_page_preview = true;
	$sendMessage->parse_mode = 'Markdown';
	$sendMessage->reply_markup = $inlineKeyboard;
	setData('step-add-coin-'.A_USER_CHAT_ID,'2');
}