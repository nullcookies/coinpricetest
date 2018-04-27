<a href="https://nguyentanthanh.tk/send_liza/index.php?send=ok">Send Mail</a>
<?php
if(isset($_GET["send"])) {
    require_once 'function.php';
    $arrayData  =   convertDataUser();
    if(!empty($arrayData)) {
        foreach($arrayData as $key => $value) {
            sendMail($value['email'], $value['name'], $value['code']);
        }
    }
    //sendMail('ngtanthanh90@gmail.com', 'Thanh Nguyen', 'aaaaaaaaaabbbbbbbcccccccc');
}
?>