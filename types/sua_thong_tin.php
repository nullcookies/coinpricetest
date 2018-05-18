<?php

use unreal4u\TelegramAPI\Telegram\Types\KeyboardButton;
use unreal4u\TelegramAPI\Telegram\Types\ReplyKeyboardMarkup;

$sendMessage->chat_id = A_USER_CHAT_ID;
/*$sendMessage->text = getCurrentUserInfo(A_USER_CHAT_ID);*/
$sendMessage->text = 'Vui lòng chọn các yêu cầu dưới đây:';
$sendMessage->reply_markup = new ReplyKeyboardMarkup();
//$sendMessage->reply_markup->one_time_keyboard = true;

for($i = 0; $i < count($nutChinhSua); $i++) {
	$keyboardButton = new KeyboardButton();
	$keyboardButton->text = $nutChinhSua[$i];
	$sendMessage->reply_markup->keyboard[$i][] = $keyboardButton;
}