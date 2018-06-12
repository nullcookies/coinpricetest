<?php

use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
$sendMessage->chat_id = A_USER_CHAT_ID;
$sendMessage->text = "Vui lòng chọn yêu cầu support (thường trả lời trong vòng 24h):\nLưu ý: Bạn phải đăng ký email để nhận trả lời từ ban quản trị, nếu chưa có vui lòng vào phần Chỉnh Sửa Thông Tin để đăng ký.";
$row = null;
$arrayInlineKeyBoard    =   [
    'inline_keyboard' => [
        [
            ['text' => '1️⃣ Bot', 'callback_data' => 'email-support_bot'],
            ['text' => '2️⃣ Lãi', 'callback_data' => 'email-support_profit'],
        ],
        [
            ['text' => '3️⃣ Bảng Tính', 'callback_data' => 'email-support_sheet'],
            ['text' => '4️⃣ Thông Tin Plan', 'callback_data' => 'email-support_information'],
        ],
        [
            ['text' => '5️⃣ Khác', 'callback_data' => 'email-support_other'],
        ],
    ]
];


$inlineKeyboard = new Markup($arrayInlineKeyBoard);

$sendMessage->disable_web_page_preview = true;
$sendMessage->parse_mode = 'Markdown';
$sendMessage->reply_markup = $inlineKeyboard;
setData('support-step-'.$queryUserId,'2');