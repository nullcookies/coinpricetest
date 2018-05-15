<?php

declare(strict_types = 1);
$updateData = json_decode(file_get_contents('php://input'), true);
$chatId       =   $updateData['message']['from']['id'];
$firstName    =   $updateData['message']['from']['first_name'];
$lastName     =   $updateData['message']['from']['last_name'];
$text         =   $updateData['message']['text'];

//Query
$query            =   $updateData['callback_query'];
$queryid          =   $query['id'];
$queryUserId      =   $query['from']['id'];
$queryUsername    =   $query['from']['username'];
$queryData        =   $query['data'];
$querymsgId       =   $query['message']['message_id'];
$querymsgText     =   $query['message']['text'];

define('BOT_TOKEN', '481065752:AAGrj0BLfzRU-OYzwQAN0-TkZqhhFU-JlcE');
define('A_USER_CHAT_ID', $chatId);
define('A_USER_MESSAGE', $text);

$nutYeuCau 	=	array(
		'📋 Xem Danh Sách Plan', // $nutYeuCau[0]
		'💰 Yêu Cầu Rút Coin', 	// $nutYeuCau[1]
		'📤 Yêu Cầu Cuối Tháng', // $nutYeuCau[2]
		'🔁 Chuyển Coin', // $nutYeuCau[3]
		'⚙️ Chỉnh Sửa Thông Tin' // $nutYeuCau[4]
	);

$nutChinhSua 	=	array(
		'📥 Sửa Số Ví', // $nutChinhSua[0]
		'📧 Sửa Email', // $nutChinhSua[1]
		'🛠 Sửa Họ Tên', // $nutChinhSua[2]
		'🛠 Sửa Facebook', // $nutChinhSua[3]
		'⚙️ Xem Thông Tin', // $nutChinhSua[4]
		'🔙 Quay Lại' // $nutChinhSua[5]
	);