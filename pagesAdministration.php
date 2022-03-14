<?php

/*
*
*        YUIN Club (yuin.yeditepe.edu.tr) 2020 yılında Mart - Haziran ayları arasında Emrecan Öksüm tarafından YUINFORMATICS (Yeditepe Üniversitesi Bilişim Kulübü) için kodlanmıştır.
*        Back-end kısmı tamamen şahsımın el emeği, göz nuru olup, front-end kısmı Colorlib tarafından tasarlanan Unica teması üzerine kurulmuştur. Makine değiliz sonuçta :)
*        Tavsiye ettiğim PHP 7.2 üzerinde çalışması ama olurda Yeditepe BiM gelecekte PHP 7.x'i terk ederse kolay bir şekilde PHP 8 veya üzerine uyumlu hale getirebilirsiniz.
*        Sakın kaşınıp bu sistemin yenisini yapmaya çalışmayın, yazdığım sistem çok sadedir, basit bir şekilde üzerinde oynamalar yapabilirsiniz. Şahsi kanaatimce bu yazılım
*        en az 10 yıl iş görür.
*        
*        yuin.yeditepe.edu.tr yazılımı 11.12.2021 tarihinde açık kaynaklı topluluk katılımına açık hale getirilmiştir.
*        ~ Emrecan Öksüm
*        ~ emreoksum.com.tr
*        ~ ben@emreoksum.com.tr
*        ~ +905344839345
*
*/

session_start();

define('YUIN',1);

// Veritabanınla bağlantı kur ve basit ayarları uygula (mesela debugging)
include "/home/yuinyeditepe/public_html/backend/connect.php";

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";

// Şifreleri getir
require_once "/home/yuinyeditepe/public_html/protected/protected_constants.php";

// Göstermelik
$yuinEmailSender = YUIN_SMTP_ACCT;
$yuinEmailPass = YUIN_SMTP_PASS;
$yuinEmailSMTP = YUIN_SMTP;

$yuinEmailPassLen = strlen($yuinEmailPass) - 4;
$yuinEmailPassMasked = $yuinEmailPass[0] . $yuinEmailPass[1] . $yuinEmailPass[2] . $yuinEmailPass[3] . str_repeat('*', $yuinEmailPassLen);

$login = 0;

if(isset($_SESSION['login']) && $_SESSION['login'] === 1) {
    
    $ip = getUserIP();
    if($_SESSION['ip'] != $ip) {
        
        header('Location: logout.php?ipc');
        exit;
    }
    $login = 1;
}

if($login == 1) {
    
    // Kullanıcı bilgilerini getir
    if($stmt = $pdo->prepare("SELECT user,name,surname,permlevel FROM users WHERE uid = :uid")) {
        
        // PDO parametrelerini ayarla
        $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $bilgi = $stmt->fetch();
        }
    }
}

if($bilgi['permlevel'] == 1) {
    
    unset($stmt);
    unset($pdo);
    echo '<script>alert("Üzgünüm!\n\nBu sayfaya ya da içeriğe erişmek için yeterli yetki seviyesine sahip değilsin. Erişim engellendi!");window.location.replace("http://yuin.yeditepe.edu.tr/index.php");</script>';
    exit;
}

if($bilgi['permlevel'] < 2) {
    
    unset($stmt);
    unset($pdo);
    header('Location: logout.php');
    exit;
}

if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'admGallery') {
    
    if(isset($_GET['galAdmId']) && !empty($_GET['galAdmId']) && is_numeric($_GET['galAdmId'])) {
        
        if($stmt = $pdo->prepare("SELECT * FROM galeri WHERE id = :id")) {
            
            $stmt->bindParam(":id", $_GET['galAdmId'], PDO::PARAM_STR);
            if($stmt->execute()) {
                
                $imgs = $stmt->fetch();
            }
        }
    }else{
        
        if($stmt = $pdo->prepare("SELECT * FROM galeri")) {
            
            if($stmt->execute()) {
                
                $imgs = $stmt->fetchAll();
            }
        }
    }
}

if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'admMagazines') {
    
    if(isset($_GET['magAdmId']) && !empty($_GET['magAdmId']) && is_numeric($_GET['magAdmId'])) {
        
        if($stmt = $pdo->prepare("SELECT * FROM magazine WHERE id = :id")) {
            
            $stmt->bindParam(":id", $_GET['magAdmId'], PDO::PARAM_STR);
            if($stmt->execute()) {
                
                $mags = $stmt->fetch();
            }
        }
    }else{
        
        if($stmt = $pdo->prepare("SELECT * FROM magazine")) {
            
            if($stmt->execute()) {
                
                $mags = $stmt->fetchAll();
            }
        }
    }
}

if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'postaci') {
    
    // Postacı için ekstra bir veri çekmemiz gerekmiyor. Bu sadece şimdilik burada dursun belki ileride lazım olur...
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $action = @$_POST['action'];
    if($action == 'editGalAct' && isset($_GET['galAdmId']) && !empty($_GET['galAdmId']) && is_numeric($_GET['galAdmId'])) {
        
        $error = '';
        $updatePhoto = false;
        $photo = @$_FILES['photo']['tmp_name'];
        if(isset($photo) && !empty($photo)) {
            
            $updatePhoto = true;
        }
        
        if($updatePhoto == true) {
            
            $photoExt = $_FILES['photo']['type'];
            
            if($_FILES['photo']['size'] < 0 || !getimagesize($_FILES['photo']['tmp_name'])) {
                
                unlink($_FILES['photo']['tmp_name']);
                $error .= "Lütfen fotoğraf yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            
            /*if(!empty($banner)) {
                
                //$error .= "Afiş boş olamaz!" . PHP_EOL;
                $updateBanner = true;
            }*/
            
            $photo = base64_encode($photo);
            unlink($_FILES['photo']['tmp_name']);
            $photo = 'data:' . $photoExt . ';base64,' . $photo;
        }
        
        $alt = trim($_POST['alt']);
        $priority = trim($_POST['priority']);
        
        if(empty($alt)) {
            
            $error .= "Fotoğraf açıklaması boş olamaz. Lütfen bir açıklama girin." . PHP_EOL;
        }
        
        if(empty($priority)) {
            
            $error .= "Fotoğraf önceliği boş olamaz. Lütfen bir öncelik girin." . PHP_EOL;
        }
        
        if(empty($error)) {
            
            $sqlqry = "UPDATE galeri SET alt = :alt, priority = :priority WHERE id = :id";
            
            if($updatePhoto == true) {
                
                $sqlqry = "UPDATE galeri SET img = :photo, alt = :alt, priority = :priority WHERE id = :id";
            }
            
            if($stmt = $pdo->prepare($sqlqry)) {
                
                if($updatePhoto == true) {
                    
                    $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                }
                
                $stmt->bindParam(":alt", $alt, PDO::PARAM_STR);
                $stmt->bindParam(":priority", $priority, PDO::PARAM_STR);
                $stmt->bindParam(":id", $_GET['galAdmId'], PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    header('Location: pagesAdministration.php?action=admGallery');
                    exit;
                }
            }
        }
    }
    
    if($action == 'addGalAct') {
        
        $error = '';
        $photo = $_FILES['photo']['tmp_name'];
        if(isset($photo) && !empty($photo)) {
            
            $photoExt = $_FILES['photo']['type'];
            
            if($_FILES['photo']['size'] < 0 || !getimagesize($_FILES['photo']['tmp_name'])) {
                
                unlink($_FILES['photo']['tmp_name']);
                $error .= "Lütfen fotoğraf yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            
            /*if(!empty($banner)) {
                
                //$error .= "Afiş boş olamaz!" . PHP_EOL;
                $updateBanner = true;
            }*/
            
            $photo = base64_encode($photo);
            unlink($_FILES['photo']['tmp_name']);
            $photo = 'data:' . $photoExt . ';base64,' . $photo;
        }else{
            
            $error .= "Lütfen fotoğraf yükleyiniz." . PHP_EOL;
        }
        
        $alt = trim($_POST['alt']);
        $priority = trim($_POST['priority']);
        
        if(empty($alt)) {
            
            $error .= "Fotoğraf açıklaması boş olamaz. Lütfen bir açıklama girin." . PHP_EOL;
        }
        
        if(empty($priority)) {
            
            $error .= "Fotoğraf önceliği boş olamaz. Lütfen bir öncelik girin." . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("INSERT into galeri (img, alt, priority) VALUES (:photo, :alt, :priority)")) {
                
                $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                $stmt->bindParam(":alt", $alt, PDO::PARAM_STR);
                $stmt->bindParam(":priority", $priority, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    header('Location: pagesAdministration.php?action=admGallery');
                    exit;
                }
            }
        }
    }
    
    if($action == 'removeGalAct' && isset($_GET['galAdmId']) && !empty($_GET['galAdmId']) && is_numeric($_GET['galAdmId'])) {
        
        $error = '';
        $delImg = $_GET['galAdmId'];
        
        if(!isset($delImg)) {
            
            $error .= "Silinmesi istenen fotoğraf belirlenmedi! Tekrar deneyin." . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("DELETE from galeri WHERE id = :id")) {
                
                $stmt->bindParam(":id", $delImg, PDO::PARAM_STR);
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    header('Location: pagesAdministration.php?action=admGallery');
                    exit;
                }
            }
        }
    }
    
    // Dergilerin yönetimi
    
    if($action == 'editMagAct' && isset($_GET['magAdmId']) && !empty($_GET['magAdmId']) && is_numeric($_GET['magAdmId'])) {
        
        $error = '';
        $updatePhoto = false;
        $updatePdf = false;
        $photo = @$_FILES['photo']['tmp_name'];
        $pdf = @$_FILES['pdf']['tmp_name'];
        if(isset($photo) && !empty($photo)) {
            
            $updatePhoto = true;
        }
        
        if(isset($pdf) && !empty($pdf)) {
            
            $updatePdf = true;
        }
        
        if($updatePhoto == true) {
            
            $photoExt = $_FILES['photo']['type'];
            
            if($_FILES['photo']['size'] < 0 || !getimagesize($_FILES['photo']['tmp_name'])) {
                
                unlink($_FILES['photo']['tmp_name']);
                $error .= "Lütfen dergi kapağını yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            
            /*if(!empty($banner)) {
                
                //$error .= "Afiş boş olamaz!" . PHP_EOL;
                $updateBanner = true;
            }*/
            
            $photo = base64_encode($photo);
            unlink($_FILES['photo']['tmp_name']);
            $photo = 'data:' . $photoExt . ';base64,' . $photo;
        }
        
        if($updatePdf == true) {
            
            if($_FILES['pdf']['size'] < 0) {
                
                unlink($_FILES['pdf']['tmp_name']);
                $error .= "Lütfen dergiyi yükleyin. Gönderim yarıda mı kesildi de pdf dosyası eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            //$pdf = fopen($_FILES['pdf']['tmp_name'], 'rb');
            //unlink($_FILES['pdf']['tmp_name']);
            $pdf = $_FILES['pdf']['tmp_name'];
            if(!file_exists($pdf)) {
                
                $error .= "Yükleme sırasında bir hata meydana geldi." . PHP_EOL;
            }else{
                
                /*if(file_exists('/home/yuinyeditepe/tmp/' . $pdffname)) {
                    
                    unlink('/home/yuinyeditepe/tmp/' . $pdffname);
                }
                
                if(move_uploaded_file($pdf, '/home/yuinyeditepe/tmp/' . $pdffname)) {
                    
                    $pdf = '/home/yuinyeditepe/tmp/' . $pdffname;
                    $pdf = file_get_contents($pdf);
                }*/
                
                $pdf = file_get_contents($pdf);
                //$pdf = fopen($pdf, 'rb');
                $pdf = base64_encode($pdf);
                unlink($_FILES['pdf']['tmp_name']);
            }
        }
        
        $alt = trim($_POST['alt']);
        $priority = trim($_POST['priority']);
        
        if(empty($alt)) {
            
            $error .= "Dergi açıklaması boş olamaz. Lütfen bir açıklama girin." . PHP_EOL;
        }
        
        if(empty($priority)) {
            
            $error .= "Dergi önceliği boş olamaz. Lütfen bir öncelik girin." . PHP_EOL;
        }
        
        if(empty($error)) {
            
            $sqlqry = "UPDATE magazine SET alt = :alt, priority = :priority WHERE id = :id";
            
            if($updatePhoto == true) {
                
                $sqlqry = "UPDATE magazine SET img = :photo, alt = :alt, priority = :priority WHERE id = :id";
            }else if($updatePdf == true) {
                
                $sqlqry = "UPDATE magazine SET pdf = LOAD_FILE(:pdf), alt = :alt, priority = :priority WHERE id = :id";
            }else if($updatePhoto == true && $updatePdf == true) {
                
                $sqlqry = "UPDATE magazine SET img = :photo, pdf = LOAD_FILE(:pdf), alt = :alt, priority = :priority WHERE id = :id";
            }
            
            if($stmt = $pdo->prepare($sqlqry)) {
                
                if($updatePhoto == true) {
                    
                    $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                }else if($updatePdf == true) {
                    
                    $stmt->bindParam(":pdf", $pdf, PDO::PARAM_LOB);
                }else if($updatePhoto == true && $updatePdf == true) {
                    
                    $stmt->bindParam(":pdf", $pdf, PDO::PARAM_LOB);
                    $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                }
                
                $stmt->bindParam(":alt", $alt, PDO::PARAM_STR);
                $stmt->bindParam(":priority", $priority, PDO::PARAM_STR);
                $stmt->bindParam(":id", $_GET['magAdmId'], PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unlink($pdf);
                    unset($stmt);
                    unset($pdo);
                    header('Location: pagesAdministration.php?action=admMagazines');
                    exit;
                }
            }
        }
    }
    
    if($action == 'addMagAct') {
        
        $error = '';
        
        $photo = $_FILES['photo']['tmp_name'];
        $pdf = $_FILES['pdf']['tmp_name'];
        
        if(isset($photo) && !empty($photo)) {
            
            $photoExt = $_FILES['photo']['type'];
            
            if($_FILES['photo']['size'] < 0 || !getimagesize($_FILES['photo']['tmp_name'])) {
                
                unlink($_FILES['photo']['tmp_name']);
                $error .= "Lütfen dergi kapağı yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            
            $photo = base64_encode($photo);
            unlink($_FILES['photo']['tmp_name']);
            $photo = 'data:' . $photoExt . ';base64,' . $photo;
            //echo 'FOTO YUKLENDI';
        }else{
            
            $error .= "Lütfen fotoğraf yükleyiniz." . PHP_EOL;
        }
        
        if(isset($pdf) && !empty($pdf)) {
            
            if($_FILES['pdf']['size'] < 0) {
                
                unlink($_FILES['pdf']['tmp_name']);
                $error .= "Lütfen dergi yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            //$pdf = fopen($_FILES['pdf']['tmp_name'], 'rb');
            //unlink($_FILES['pdf']['tmp_name']);
            $pdf = $_FILES['pdf']['tmp_name'];
            if(!file_exists($pdf)) {
                
                $error .= "Yükleme sırasında bir hata meydana geldi." . PHP_EOL;
            }else{
                
                /*if(file_exists('/home/yuinyeditepe/tmp/' . $pdffname)) {
                    
                    unlink('/home/yuinyeditepe/tmp/' . $pdffname);
                }
                
                if(move_uploaded_file($pdf, '/home/yuinyeditepe/tmp/' . $pdffname)) {
                    
                    $pdf = '/home/yuinyeditepe/tmp/' . $pdffname;
                    $pdf = file_get_contents($pdf);
                }*/
                
                $pdf = file_get_contents($pdf);
                //$pdf = fopen($pdf, 'rb');
                $pdf = base64_encode($pdf);
                unlink($_FILES['pdf']['tmp_name']);
            }
        }else{
            
            $error .= "Lütfen dergi yükleyiniz." . PHP_EOL;
        }
        
        $alt = trim($_POST['alt']);
        $priority = trim($_POST['priority']);
        
        if(empty($alt)) {
            
            $error .= "Dergi açıklaması boş olamaz. Lütfen bir açıklama girin." . PHP_EOL;
        }
        
        if(empty($priority)) {
            
            $error .= "Dergi önceliği boş olamaz. Lütfen bir öncelik girin." . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("INSERT into magazine (img, pdf, alt, priority) VALUES (:photo, :pdf, :alt, :priority)")) {
                
                $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                $stmt->bindParam(":pdf", $pdf, PDO::PARAM_LOB);
                $stmt->bindParam(":alt", $alt, PDO::PARAM_STR);
                $stmt->bindParam(":priority", $priority, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unlink($pdf);
                    unset($stmt);
                    unset($pdo);
                    header('Location: pagesAdministration.php?action=admMagazines');
                    exit;
                }
            }
        }
    }
    
    if($action == 'removeMagAct' && isset($_GET['magAdmId']) && !empty($_GET['magAdmId']) && is_numeric($_GET['magAdmId'])) {
        
        $error = '';
        $delMag = $_GET['magAdmId'];
        
        if(!isset($delMag)) {
            
            $error .= "Silinmesi istenen dergi belirlenmedi! Tekrar deneyin." . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("DELETE from magazine WHERE id = :id")) {
                
                $stmt->bindParam(":id", $delMag, PDO::PARAM_STR);
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    header('Location: pagesAdministration.php?action=admMagazines');
                    exit;
                }
            }
        }
    }
    
    if($action == 'postaci') {
        
        $error = '';
        
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(file_exists($pdf)) {
        
        unlink($pdf);
    }
    unset($stmt);
    unset($pdo);
    echo $error;
    exit;
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Sayfaları yönet</title>
	<meta charset="UTF-8">
	<meta name="description" content="Yeditepe Üniversitesi Bilişim Kulübü YUINFORMATICS'e hoş geldiniz!">
	<meta name="keywords" content="yeditepe bilişim,yuin,yeditepe yuin,bilişim kulübü,bilgisayar kulübü,yuinformatics,informatics yeditepe">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Favicon -->   
	<link href="img/favicon.ico" rel="shortcut icon"/>

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i" rel="stylesheet">

	<!-- Stylesheets -->
	<link rel="stylesheet" href="css/bootstrap.min.css"/>
	<link rel="stylesheet" href="css/font-awesome.min.css"/>
	<script src="https://kit.fontawesome.com/f65dc3fbad.js" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="css/themify-icons.css"/>
	<link rel="stylesheet" href="css/magnific-popup.css"/>
	<link rel="stylesheet" href="css/animate.css"/>
	<link rel="stylesheet" href="css/owl.carousel.css"/>
	<link rel="stylesheet" href="css/style.css"/>


	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>
<body>
	<!-- Page Preloder -->
	<div id="preloder">
		<div class="loader"></div>
	</div>

	<!-- header section -->
	<header class="header-section">
		<div class="container">
			<!-- logo -->
			<a href="index.php" class="site-logo"><img src="img/logo.png" style="width:40%;height:40%;" alt="Bilişim Kulübü Logo"></a>
			<div class="nav-switch">
				<i class="fa fa-bars"></i>
			</div>
			<div class="header-info">
				<div class="hf-item">
					<i class="fa fa-map-marker"></i>
					<p><span>Kampüsteki konum:</span>Ticari Bilimler Fakültesi 1. Kat Z16-B</p>
				</div>
			</div>
		</div>
	</header>
	<!-- header section end-->


	<!-- Header section  -->
	<nav class="nav-section">
		<div class="container">
			<div class="nav-right">
				<?php
				
				    // PHP Başlangıç
				    
				    if($login == 0) {
				        
				    ?>
				    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş yap</a></li>
				    <?php
				    
				    }else{
				    ?>
				    
				    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış yap</a></li>
				    
				    <?php
				    }
				    
				    // PHP Bitiş
				    ?>
			</div>
			<ul class="main-menu">
				<?=/* Ziyaretçi navigasyon barını göster */ file_get_contents('tmpller/ziyaretciNavbar.tmpl');?>
			</ul>
		</div>
	</nav>
	<!-- Header section end -->
	
	<?php
	
	if($login == 1) {
	
	?>
	<!-- Additional header section  -->
	<nav class="nav-section">
		<div class="container">
			<div class="nav-right">
			    <li><a href=""> Tekrardan hoşgeldiniz sayın <?=$bilgi['name'] . ' ' . $bilgi['surname'];?></a></li>
			</div>
			<ul class="main-menu">
				<?=/* Üye navigasyon barını göster */ file_get_contents('tmpller/uyeEkMenu.tmpl');?>
			</ul>
		</div>
	</nav>
	<!-- Additional header section end -->
	<!-- Additional header section  -->
	<nav class="nav-section">
		<div class="container">
			<!--<div class="nav-right">
			    <li><a href=""> Ek yönetici paneli</a></li>
			</div>-->
			<ul class="main-menu">
				<?=/* YK ek menü navigasyon barını göster */ file_get_contents('tmpller/ykUyeAltEkMenu.tmpl');?>
			</ul>
		</div>
	</nav>
	<!-- Additional header section end -->
	<?php
	
	}
	
	?>


	<!-- Breadcrumb section -->
	<div class="site-breadcrumb">
		<div class="container">
			<a href="#"><i class="fa fa-home"></i> Ana Sayfa</a> <i class="fa fa-angle-right"></i> YK Panel <i class="fa fa-angle-right"></i>
			<span>Sayfaları yönet</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->
	
	<script>
	
	function sleepold(milliseconds) {
        
        const date = Date.now();
        let currentDate = null;
        do {
            
            currentDate = Date.now();
        } while (currentDate - date < milliseconds);
    }
    
    function sleep(ms) {
        
        return new Promise(resolve => setTimeout(resolve, ms));
    }
	
	function HttpPostMailYolla(i, total, recipientMail, mailTitle, mailIcerik) {
	    
	    $(document).ready(function(){
                $.post("activityAdministration.php",
                    {
                        action: "sendMail",
                        email: recipientMail,
                        title: mailTitle,
                        contenttt: mailIcerik,
                    },
                function(data, status){
                    
                    if(data.includes("basariyla")) {
                        
                        console.log("Eposta gonderimi " + recipientMail + " adresine basarili.");
	                    lastSent = document.getElementById("mailAlertBox").innerHTML;
                        lastSent = lastSent + "<b>(" + (i + 1) + "/" + total + ")</b> " + recipientMail + " gönderim başarılı<br>";
                        document.getElementById("mailAlertBox").innerHTML = lastSent;
                        
                        return true;
                    }else{
                        
                        alert("Eposta gönderimi başarısız oldu: " + recipientMail + "   " + status + "\n\nOlay logu: \n" + data);
                    }
                    
                    return false;
                });
                
            });
	}
	
	function sendEmailTekerTeker() {
	    
	    var recipients = document.getElementById("recipient").value;
	    var mailTitle = document.getElementById("title").value;
	    var mailIcerik = document.getElementById("contentt").value;
	    
	    recipients = recipients.toString();
	    recipients = recipients.split(',');
	    
	    if(recipients == undefined) {
	        
	        alert("Alıcı listesinde bir yanlışlık var! Epostaları virgülle doğru bir şekilde ayırdığınızdan eminmisiniz?\nLütfen kontrol edip tekrar deneyiniz.");
	        return;
	    }
	    
	    if(mailTitle == undefined) {
	        
	        alert("Eposta konusu kısmında bir eksiklikmi var? İçeriği çekemedim.\nLütfen kontrol edip tekrar deneyiniz.");
	        return;
	    }
	    
	    if(mailIcerik == undefined) {
	        
	        alert("Eposta içeriği kısmında bir eksiklikmi var? İçeriği çekemedim.\nLütfen kontrol edip tekrar deneyiniz.");
	        return;
	    }
	    
	    var lastSent;
	    
	    document.getElementById("epostaGonderiWarningTag").style.display="block";
	    console.log( JSON.stringify(recipients, null, 2) ); // Debug
	    
	    for(var i = 0; i < recipients.length; i++) {
	        
	        console.log("Eposta gonderimi " + recipients[i] + " adresine başlatıldı.");
	        HttpPostMailYolla(i, recipients.length, recipients[i], mailTitle, mailIcerik);
	        
	    }
	}
	
	</script>
	
	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>Sayfaları yönet</h3>
					
				</div>
				<hr>
				<center>
				<h3>Fotoğraf galerisini yönet</h3>
				<?php
				
				if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'admGallery') {
				    ?>
				    <table>
				        <tr>
				            <td>ID</td>
				            <td>Fotoğraf</td>
				            <td>Açıklama</td>
				            <td>Öncelik</td>
				            <td>Yönet</td>
				        </tr>
				    <?php
				    
				    if(isset($_GET['galAdmId']) && !empty($_GET['galAdmId']) && is_numeric($_GET['galAdmId'])) {
				        
				        ?>
				        
				        <h3>Spesifik bir fotoğrafı yönet</h3>
				        <p>Fotoğraf ID: <?=$_GET['galAdmId'];?></p>
				        <?php
				        
				        if(isset($error)) {
				            
				            ?>
				            <p style="color:red;"><?=$error;?></p>
				            <?php
				        }
				        
				        ?>
				        </table>
				        <form method="post">
				            
				            <input type="hidden" name="action" value="removeGalAct">
				            <button class="site-btn" style="background: #b00c00;"><i class="far fa-trash-alt"></i> Fotoğrafı sil</button>
				        </form>
				        <form class="comment-form --contact" method="post" enctype="multipart/form-data">
				            
				            <input type="hidden" name="action" value="editGalAct">
				            <div class="col-lg-6">
				                <label for="galPhoto"><p>Fotoğraf <b>(Sadece fotoğrafı değiştirmek için kullanın yoksa boş bırakın)</b></p></label>
					            <img src="<?=$imgs['img'];?>" style="width:40%;height:40%;"><br>
					            <br>
					            <input type="file" id="galPhoto" name="photo">
				            </div>
				            <div class="col-lg-6">
				                <label for="alt"><p>Fotoğraf açıklaması (alt etiketi)</p></label>
				                <input type="text" id="alt" name="alt" value="<?=$imgs['alt'];?>" required>
				            </div>
				            <div class="col-lg-6">
				                <label for="priority"><p>Öncelik</p></label>
				                <input type="text" id="priority" name="priority" value="<?=$imgs['priority'];?>" required>
				            </div>
				            <div class="col-lg-6">
				                <button class="site-btn">Güncelle</button>
				            </div>
				        </form>
				        <?php
				    }else{
				        
				        foreach($imgs as $img) {
				            
				            ?>
				            
				            <tr>
				                <td><?=$img['id'];?></td>
				                <td><img src="<?=$img['img'];?>" style="width:10%;height:10%;"></td>
				                <td><?=$img['alt'];?></td>
				                <td><?=$img['priority'];?></td>
				                <td><a href="pagesAdministration.php?action=admGallery&galAdmId=<?=$img['id'];?>"><button class="site-btn">YÖNET</button></a></td>
				            </tr>
				            
				            <?php
				        }
				        ?>
				        </table>
				        <hr>
				        <h3>Galeriye yeni fotoğraf yükle</h3>
				        <?php
				        
				        if(isset($error)) {
				            
				            ?>
				            <p style="color:red;"><?=$error;?></p>
				            <?php
				        }
				        
				        ?>
				        <form class="comment-form --contact" method="post" enctype="multipart/form-data">
				            
				            <input type="hidden" name="action" value="addGalAct">
				            <div class="col-lg-6">
				                <label for="galPhoto"><p>Fotoğraf</p></label>
					            <input type="file" id="galPhoto" name="photo">
				            </div>
				            <div class="col-lg-6">
				                <label for="alt"><p>Fotoğraf açıklaması (alt etiketi)</p></label>
				                <input type="text" id="alt" name="alt" value="" required>
				            </div>
				            <div class="col-lg-6">
				                <label for="priority"><p>Öncelik</p></label>
				                <input type="text" id="priority" name="priority" value="" required>
				            </div>
				            <div class="col-lg-6">
				                <button class="site-btn">Yükle</button>
				            </div>
				        </form>
				        <?php
				    }
				}else{
				    
				    ?>
				    <br>
				    <a href="pagesAdministration.php?action=admGallery"><button class="site-btn">YÖNET</button></a>
				    <?php
				}
				?>
				<br>
				<h3>Dergileri yönet</h3>
				<?php
				
				if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'admMagazines') {
				    ?>
				    <table>
				        <tr>
				            <td>ID</td>
				            <td>Fotoğraf</td>
				            <td>PDF</td>
				            <td>Açıklama</td>
				            <td>Öncelik</td>
				            <td>Yönet</td>
				        </tr>
				    <?php
				    
				    if(isset($_GET['magAdmId']) && !empty($_GET['magAdmId']) && is_numeric($_GET['magAdmId'])) {
				        
				        ?>
				        
				        <h3>Spesifik bir dergi sayısını yönet</h3>
				        <p>Dergi ID: <?=$_GET['magAdmId'];?></p>
				        <?php
				        
				        if(isset($error)) {
				            
				            ?>
				            <p style="color:red;"><?=$error;?></p>
				            <?php
				        }
				        
				        ?>
				        </table>
				        <form method="post">
				            
				            <input type="hidden" name="action" value="removeMagAct">
				            <button class="site-btn" style="background: #b00c00;"><i class="far fa-trash-alt"></i> Dergi sayısını sil</button>
				        </form>
				        <form class="comment-form --contact" method="post" enctype="multipart/form-data">
				            
				            <input type="hidden" name="action" value="editMagAct">
				            <div class="col-lg-6">
				                <label for="galPhoto"><p>Dergi kapağı <b>(Sadece fotoğrafı değiştirmek için kullanın yoksa boş bırakın)</b></p></label>
					            <img src="<?=$mags['img'];?>" style="width:40%;height:40%;"><br>
					            <br>
					            <input type="file" id="galPhoto" name="photo">
				            </div>
				            <div class="col-lg-6">
				                <label for="galPhoto"><p>Dergi PDF <b>(Sadece PDF'i değiştirmek için kullanın yoksa boş bırakın)</b></p></label>
					            <a href="getPdfData.php?pdfid=<?=$mags['id'];?>" target="_blank">PDF'i gör</a>
					            <br>
					            <input type="file" id="galPhoto" name="pdf">
				            </div>
				            <div class="col-lg-6">
				                <label for="alt"><p>Dergi açıklaması (alt etiketi)</p></label>
				                <input type="text" id="alt" name="alt" value="<?=$mags['alt'];?>" required>
				            </div>
				            <div class="col-lg-6">
				                <label for="priority"><p>Öncelik</p></label>
				                <input type="text" id="priority" name="priority" value="<?=$mags['priority'];?>" required>
				            </div>
				            <div class="col-lg-6">
				                <button class="site-btn">Güncelle</button>
				            </div>
				        </form>
				        <?php
				    }else{
				        
				        foreach($mags as $mag) {
				            
				            ?>
				            
				            <tr>
				                <td><?=$mag['id'];?></td>
				                <td><img src="<?=$mag['img'];?>" style="width:10%;height:10%;"></td>
				                <td><a href="getPdfData.php?pdfid=<?=$mag['id'];?>" target="_blank">PDF'i gör</a></td>
				                <td><?=$mag['alt'];?></td>
				                <td><?=$mag['priority'];?></td>
				                <td><a href="pagesAdministration.php?action=admMagazines&magAdmId=<?=$mag['id'];?>"><button class="site-btn">YÖNET</button></a></td>
				            </tr>
				            
				            <?php
				        }
				        ?>
				        </table>
				        <hr>
				        <h3>Siteye yeni dergi yükle</h3>
				        <?php
				        
				        if(isset($error)) {
				            
				            ?>
				            <p style="color:red;"><?=$error;?></p>
				            <?php
				        }
				        
				        ?>
				        <form class="comment-form --contact" method="post" enctype="multipart/form-data">
				            
				            <input type="hidden" name="action" value="addMagAct">
				            <div class="col-lg-6">
				                <label for="magCase"><p>Dergi kapağı</p></label>
					            <input type="file" id="magCase" name="photo">
				            </div>
				            <div class="col-lg-6">
				                <label for="magPdf"><p>Dergi PDF</p></label>
					            <br>
					            <input type="file" id="magPdf" name="pdf">
				            </div>
				            <div class="col-lg-6">
				                <label for="alt"><p>Dergi açıklaması (alt etiketi)</p></label>
				                <input type="text" id="alt" name="alt" value="" required>
				            </div>
				            <div class="col-lg-6">
				                <label for="priority"><p>Öncelik</p></label>
				                <input type="text" id="priority" name="priority" value="" required>
				            </div>
				            <div class="col-lg-6">
				                <button class="site-btn">Yükle</button>
				            </div>
				        </form>
				        <?php
				    }
				}else{
				    
				    ?>
				    <br>
				    <a href="pagesAdministration.php?action=admMagazines"><button class="site-btn">YÖNET</button></a>
				    <?php
				}
				?>
				<br>
				<h3>Postacı</h3>
				<?php
				
				if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'postaci') {
				    
				    ?>
				    <hr>
				    <form id="epostaForm" class="comment-form --contact" method="post" enctype="multipart/form-data">
				        <input type="hidden" name="action" value="sendMail">
				        <h5>Herhangi bir eposta adresine eposta gönder</h5>
				        <br>
				        <h6>DİKKAT! Gönderilecek eposta, <b><?=$yuinEmailSender;?></b> eposta hesabının, <b><?=$yuinEmailPassMasked;?></b> şifresi ile <b><?=$yuinEmailSMTP;?></b> SMTP sunucusu üzerinden gönderilecektir. Eğer bilgiler güncel değilse <b>protected/protected_constants.php</b> dosyasından güncelleyiniz!</h6>
				        <br>
				        <div class="col-lg-6">    
				            <div style="display: none;" id="epostaGonderiWarningTag" class="alert alert-warning">
                                <p id="mailAlertBox">Tüm katılımcılara eposta gönderiliyor. Her eposta 5 saniyede bir gönderilir. Lütfen hedef sayıya ulaşana kadar sayfadan ayrılmayınız.<br><b>Bu işlem esnasında tarayıcınız 5 saniye kadar süreyle donabilir, lütfen sayfayı terk etmeyin ya da durdurmayın.</b><br></p>
                            </div>
                        </div>
				        <div class="col-lg-6">
				            <label for="recipient"><p>Alıcılar <b>(Her bir alıcıyı virgülle ayırın)</b></p></label><br>
					        <input type="text" id="recipient" name="recp" value="" placeholder="Eposta alıcıları" required>
				        </div>
				        <div class="col-lg-6">
				            <label for="title"><p>Eposta konusu</p></label><br>
					        <input type="text" id="title" name="title" value="" placeholder="Eposta konusu" required>
				        </div>
				        <div class="col-lg-6">
				            <label for="contentt"><p>Eposta içeriği <b>(HTML tagleri kullanabilirsiniz)</b></p></label><br>
					        <textarea id="contentt" name="contentt" required>


<pre><i>____________________________________
 
 Sevgilerimizle,
Yeditepe Üniversitesi Bilişim Kulübü 

https://yuin.yeditepe.edu.tr
https://www.instagram.com/YuInformatics
https://www.linkedin.com/in/YuBilisimKulubu
https://www.twitter.com/YuInformatics</i></pre>
					        </textarea>
				        </div>
				        
				        <br>
				    </form>
				    <button id="invokejQryBtn" class="site-btn" onclick="sendEmailTekerTeker()">Eposta Gönder</button>
				    
				    <?php
				    
				}else{
				    
				    ?>
				    <br>
				    <a href="pagesAdministration.php?action=postaci"><button class="site-btn">YÖNET</button></a>
				    <?php
				}
				?>
				<br>
			</center>
		</div>
	</section>
	<!-- Courses section end-->
    
	<?=/* Footer kısmını göster */ file_get_contents('tmpller/footer.tmpl');?>
    
	<!--====== Javascripts & Jquery ======-->
	<script src="js/jquery-3.2.1.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/jquery.countdown.js"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/magnific-popup.min.js"></script>
	<script src="js/main.js"></script>


	<!-- load for map -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0YyDTa0qqOjIerob2VTIwo_XVMhrruxo"></script>
	<script src="js/map.js"></script>
	
</body>
</html>