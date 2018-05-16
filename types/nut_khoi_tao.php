<?php

use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;
$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = 'Xin chào '.$firstName . ' ' . $lastName;
$sendMessage->reply_markup = new ReplyKeyboardMarkup();
//$sendMessage->reply_markup->one_time_keyboard = true;
$sendMessage->reply_markup->resize_keyboard  = true;
for($i = 0; $i < count($nutKhoiTao); $i++) {
	$keyboardButton = new KeyboardButton();
	$keyboardButton->text = $nutKhoiTao[$i];
	$sendMessage->reply_markup->keyboard[$i][] = $keyboardButton;
}