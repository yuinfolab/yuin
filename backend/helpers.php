<?php

if(!defined("YUIN"))
    die("Güvenlik sebebiyle bu dosyaya direkt erişime izin verilmemektedir.");

// Bu dosyadaki gizli bilgiler PHP sabitleri ile korunmaktadır.
require_once "/home/yuinyeditepe/public_html/protected/protected_constants.php";

require '/home/yuinyeditepe/phpmailer/PHPMailer.php';
require '/home/yuinyeditepe/phpmailer/Exception.php';
require '/home/yuinyeditepe/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getUserIP() {
    
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }
    
    return $ip;
}

function sendEmail($to, $body, $subject) {
    
    $from = YUIN_SMTP_ACCT;
    
    $mail = new PHPMailer(true);

    try {
        
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->SMTPDebug = 2;
        $mail->isSMTP();                                            
        $mail->Host = gethostbyname(YUIN_SMTP);
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = $from;                     
        $mail->Password   = YUIN_SMTP_PASS;                               
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->SMTPSecure = YUIN_SMTP_PROTO;
        $mail->Port       = YUIN_SMTP_PORT;                                    
        
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            )
        );
        
        //Recipients
        $mail->setFrom($from, 'YUInformatics Club');
        $mail->addAddress($to);
        
        // Attachments
        /*$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        $mail->addAttachment('/tmp/image.jpg', 'new.jpg');*/    // Optional name
        
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->CharSet = "utf-8";
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return 1;
    } catch (Exception $e) {
        
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return 0;
    }
}

function send_http_request($url, $payload, $method, $timeout) {
	
	if($method == 'post') {
		
		$post = true;
	}else{
		
		$post = false;
	}
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'YUINClub - Web Helper');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, $post);
	
	if(!empty($payload)):
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, @http_build_query($payload));
	endif;
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	
	if(curl_errno($ch) == 0) {
		
		$ch_res = curl_exec($ch);
		curl_close($ch);
		return $ch_res;
		
	}else{
		
		curl_close($ch);
		return false;
	}
}

function gRecaptchaVerify($response) {
    
    $secret = YUIN_RECAPTCHA_SECRET;
    $verifyResponse = send_http_request('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response'],null,'get',30);
    $verifyResponse = json_decode($verifyResponse);
    if(!$verifyResponse->success) {
        
        return 0;
    }
    
    return 1;
}

function chkUnsafePass($pass) {
    
    if(empty($pass)) {
        
        return false;
    }
    
    if(strlen($pass) < 6) {
        
        return 'Şifreniz 5 karakterden daha uzun (en az 6 karakter) olmak zorundadır.' . PHP_EOL;
    }
    
    if($pass == '123456' || $pass == '1234567' || $pass == '12345678' || $pass == '123456789' || $pass == '1234567890') {
        
        return 'Şifreniz sıralı rakamlardan oluşamaz.' . PHP_EOL;
    }
    
    if($pass == 'password' || $pass == 'sifree') {
        
        return 'Şifreniz basit olamaz.' . PHP_EOL;
    }
    
    return true;
}

function replace_tr($text) {
   
   $text = trim($text);
   $search = array('Ç','ç','Ğ','ğ','ı','İ','Ö','ö','Ş','ş','Ü','ü');
   $replace = array('C','c','G','g','i','I','O','o','S','s','U','u');
   $new_text = str_replace($search,$replace,$text);
   
   return $new_text;
} 

// Hiçbir zaman kullanılmadı :((
function getGravatarLink($mail) {
    
    $mail = md5($mail);
    $mail = urlencode($mail);
    $mail = 'https://www.gravatar.com/avatar/' . $mail;
    
    return $mail;
}

