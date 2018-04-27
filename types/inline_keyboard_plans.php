<?php

use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;

$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = 'Danh sách các Plan bạn đang tham gia';
// 1 2 3
// 4 5 6
// 7 8 9
//   0
$row = null;
$arrayInlineKeyBoard    =   array();
$plansArray             =   checkDetailPlan(A_USER_CHAT_ID);
foreach($plansArray as $key => $value) {
    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['text']               =   strtoupper($value['ten_plan']);
    $arrayInlineKeyBoard['inline_keyboard'][$key][$key]['callback_data']      =   'print_'.$value['ten_plan'];
}

/*$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = json_encode($arrayInlineKeyBoard);
*/// Choose one of the following 2 methods:

// METHOD 1: all in once with an array construction
$inlineKeyboard = new Markup($arrayInlineKeyBoard);
// METHOD 2: in parts, working directly with the object
/*
$inlineKeyboard = new Markup();
for ($i = 1; $i < 10; $i++) {
    $inlineKeyboardButton = new Button();
    $inlineKeyboardButton->text = (string)$i;
    $inlineKeyboardButton->callback_data = 'k='.(string)$i;

    $row[] = $inlineKeyboardButton;
    if (count($row) > 2) {
        $inlineKeyboard->inline_keyboard[] = $row;
        $row = null;
    }
}

$inlineKeyboardButton = new Button();
$inlineKeyboardButton->text = '0';
$inlineKeyboardButton->callback_data = 'k=0';
$inlineKeyboard->inline_keyboard[][] = $inlineKeyboardButton;
*/
$sendMessage->disable_web_page_preview = true;
$sendMessage->parse_mode = 'Markdown';
$sendMessage->reply_markup = $inlineKeyboard;