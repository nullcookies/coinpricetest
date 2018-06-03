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

$CoinExchangeFee  =	  0.005;

define('BOT_TOKEN', '481065752:AAGrj0BLfzRU-OYzwQAN0-TkZqhhFU-JlcE');
define('A_USER_CHAT_ID', $chatId);
define('A_USER_MESSAGE', $text);
define('COIN_FEE', $CoinExchangeFee);

$nutKhoiTao =	array(
		'🔐 Đăng Nhập', // $nutKhoiTao[0]
		'📝 Đăng Ký'	   // $nutKhoiTao[1]
	);

$nutYeuCau 	=	array(
		'📋 Xem Danh Sách Plan', // $nutYeuCau[0]
		'💰 Yêu Cầu Rút Coin', 	// $nutYeuCau[1]
		'📤 Yêu Cầu Cuối Tháng', // $nutYeuCau[2]
		'🔁 Chuyển Coin', // $nutYeuCau[3]
		'⚙️ Chỉnh Sửa Thông Tin',
		'✍️ Đăng Ký Thêm Coin' // $nutYeuCau[4]
	);

$nutChinhSua 	=	array(
	 	'🔏 Đổi Password', //$nutChinhSua[0]
		'📥 Sửa Số Ví', // $nutChinhSua[1]
		'📧 Sửa Email', // $nutChinhSua[2]
		'🛠 Sửa Họ Tên', // $nutChinhSua[3]
		'🛠 Sửa Facebook', // $nutChinhSua[4]
		'⚙️ Xem Thông Tin', // $nutChinhSua[5]
		'🔙 Quay Lại' // $nutChinhSua[6]
	);