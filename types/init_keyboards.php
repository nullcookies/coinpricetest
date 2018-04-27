<?php

use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;
$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = 'Xin chào '.$firstName . ' ' . $lastName;
$sendMessage->reply_markup = new ReplyKeyboardMarkup();
//$sendMessage->reply_markup->one_time_keyboard = true;

for($i = 0; $i < count($nutYeuCau); $i++) {
	$keyboardButton = new KeyboardButton();
	$keyboardButton->text = $nutYeuCau[$i];
	$sendMessage->reply_markup->keyboard[$i][] = $keyboardButton;
}