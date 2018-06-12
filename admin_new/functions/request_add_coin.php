<?php
// Cac phuong thuc telegram
include __DIR__.'/../vendor/autoload.php';
include __DIR__.'/../database/config.inc.php'; // Database Config
include __DIR__.'/../database/Database.php'; // Class Database
include __DIR__.'/../settings.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function siteUrl() {
	// base directory
	$base_dir = __DIR__;

	// server protocol
	$protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';

	// domain name
	$domain = $_SERVER['SERVER_NAME'];

	// base url
	//$base_url = preg_replace("!^${doc_root}!", '', $base_dir);

	// server port
	$port = $_SERVER['SERVER_PORT'];
	$disp_port = ($protocol == 'http' && $port == 80 || $protocol == 'https' && $port == 443) ? '' : ":$port";

	// put em all together to get the complete base URL
	$url = "${protocol}://${domain}${disp_port}";

	return $url;
}

function getDataRegister() {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $arrayPlans     =   array();
    $queryData = $db->query("SELECT * FROM :table",['table'=>'dangkycoin'])->fetch_all();

    return $queryData;
    $db->close();
}

function getInfoRegister($idDangKy) {
    $db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $arrayPlans     =   array();
    $queryData = $db->query("SELECT * FROM :table WHERE `id` = ':id'",['table'=>'dangkycoin', 'id'=>$idDangKy])->fetch();

    return $queryData;
    $db->close();
}

function updateAdminApprove($idDangKy) {
    $result           =       false;
    $db               =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
    $result           =       $db->update('dangkycoin',['admin_approve'=>1]," id = '".$idDangKy."'");
    return $result;
    $db->close();
}

function sendEmailToUser($userName, $userEmail, $tenPlan, $soCoinThem, $txtId, $ngayYeuCau) {
  $db                                 =       new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
  $mail                               =       new PHPMailer(true);
  $result                             =       false;
  $tenPlan                            =       strtoupper($tenPlan);
  try {
      $mail->SMTPOptions = array(
      'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
          )
      );
      $mail->isSMTP();
      $mail->Host       =   'smtp.gmail.com';  //gmail SMTP server
      $mail->SMTPAuth   =   true;
      $mail->Username   =   'ta.team.rb@gmail.com';   //username
      $mail->Password   =   'lyhxxnogvslxvfaz';   //password
      $mail->SMTPSecure =   'ssl';
      $mail->Port       =   465;                    //smtp port
      $mail->CharSet    =   'UTF-8';
      $mail->setFrom('ta.team.rb@gmail.com', 'TeamTA Telegram Bot');
      $mail->addAddress($userEmail, $userName);

      /*$mail->addAttachment(__DIR__ . '/attachment1.png');
      $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

      $mail->isHTML(true);

      $mail->Subject = "Xác nhận yêu cầu thêm coin - user: ".$userName;
      $mail->Body    = "Xin chào ".$userName."<br />Yêu cầu thêm coin của bạn được xác nhận với những thông tin như sau:<br />Số coin yêu cầu thêm: <b>".$soCoinThem." ".$tenPlan."</b><br />TxtId: <b>".$txtId."</b><br />Mọi thắc mắc xin vui lòng gửi mail về email: <b>ta.team.rb@gmail.com</b><br />Xin cám ơn !";

      if (!$mail->send()) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      } else {
          $result   =   true;
      }
  } catch (Exception $e) {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  }
    return $result;
  $db->close();
}
?>