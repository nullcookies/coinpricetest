<?php
//Import PHPMailer classes into the global namespace
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once 'vendor/autoload.php';
function sendMail($email, $name, $code) {
    $mail = new PHPMailer(true);
    $today = date("d/m/Y");
    try {
        $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  //gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'ta.team.rb@gmail.com';   //username
        $mail->Password = 'lyhxxnogvslxvfaz';   //password
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;                    //smtp port

        $mail->setFrom('ta.team.rb@gmail.com', 'Team Ta');
        $mail->addAddress($email, $name);

        /*$mail->addAttachment(__DIR__ . '/attachment1.png');
        $mail->addAttachment(__DIR__ . '/attachment2.jpg');*/

        $mail->isHTML(true);

        $mail->Subject = "Code Liza - $today";
        $mail->Body    = "Xin chào $name ! <br /> Code lãi liza ngày $today của bạn là: <b>$code</b><br />Nếu có gì thắc mắc xin vui lòng gửi mail về email: ta.team.rb@gmail.com <br />Xin cám ơn !";

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo "<h4>Đã gửi mail cho $email - Tên: $name - code: $code</h4>";
        }
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

function getUserData() {
    require 'vendor/autoload.php';

    $service_account_file = 'client_services.json';

    //$spreadsheet_id = '1m_zf3zUJa4iHemxzDSHPJ9KHhN0868ShNoeqc7tQ-kQ';
    $spreadsheet_id = "1qhszdO8yYCNG64Oa9Fm77duFsYuqvp2sVnqgsj5kNDw";

    //$spreadsheet_range = 'Buzz kì 6';
    $spreadsheet_range = "liza";

    $arrayData  = array();

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);
    $result = $service->spreadsheets_values->get($spreadsheet_id, $spreadsheet_range);
    $arrayData = $result->getValues(); // Mang du lieu

    return $arrayData;
}

function convertDataUser() {
    $arrayUser      =   getUserData();
    $arrayResult    =    array();
    for($i = 0; $i < 11; $i++) {
        unset($arrayUser[$i]);
    }
    $arrayUser  =   array_values($arrayUser);
    foreach($arrayUser as $key => $value) {
        if(array_key_exists('9', $value)) {
            $arrayResult[$key]['name']      =      $value['0'];
            $arrayResult[$key]['email']     =      $value['4'];
            $arrayResult[$key]['code']      =      $value['9'];
        } else {
            continue;
        }
    }
    $arrayResult    =   array_values($arrayResult);
    return $arrayResult;
}
?>