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

/*

Buranın amacı yuin.yeditepe.edu.tr içerisinde üyelerin birbiri ile ve yönetim kadrosu üyeleri ile mesajlaşabilmelerini sağlamaktı aynı OBS'de öğrenciler ile hocalar arasındaki gibi.
Sonra WhatsApp'ın daha pratik olacağını düşünmem sonrası zamanımı buna harcamamaya karar verdim. Çünkü OBS'de olan mesajlaşma özelliği bile bence planlandığı gibi kullanılmıyor...

Yine de eğitim amacıyla ya da meraklısı için yazdığım kodları incelemek belki ilginç olabilir diye düşünerek bu dosyayı silmemeye karar verdim.

*/

session_start();

define('YUIN',1);

// Veritabanınla bağlantı kur ve basit ayarları uygula (mesela debugging)
include "/home/yuinyeditepe/public_html/backend/connect.php";

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";

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
    if($stmt = $pdo->prepare("SELECT user,name,surname FROM users WHERE uid = :uid")) {
        
        // PDO parametrelerini ayarla
        $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $bilgi = $stmt->fetch();
        }
    }
}else{
    
    unset($stmt);
    unset($pdo);
    header('Location: login.php?loginToProceed');
    exit;
}

if($stmt = $pdo->prepare("SELECT * FROM yuinchat INNER JOIN users ON yuinchat.uid=users.uid INNER JOIN adminInfo ON users.uid=adminInfo.id")) {
    
    if($stmt->execute()) {
        
        $messages = $stmt->fetchAll();
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $error = '';
    
    $msg = trim($_POST['msg']);
    
    if(empty($msg)) {
        
        $error .= "Mesaj boş olamaz!" . PHP_EOL;
    }
    
    if(!gRecaptchaVerify($_POST['g-recaptcha-response'])) {
        
        $error .= "Captcha doğrulaması başarısız oldu! lütfen tekrar dene." . PHP_EOL;
    }
    
    if(empty($error)) {
        
        $time = time();
        if($stmt = $pdo->prepare("INSERT into yuinchat (uid, time, message) VALUES (:uid, :time, :message)")) {
            
            $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
            $stmt->bindParam(":time", $time, PDO::PARAM_STR);
            $stmt->bindParam(":message", $msg, PDO::PARAM_STR);
            if($stmt->execute()) {
                
                unset($stmt);
                unset($pdo);
                header('Location: yuinchat.php');
                exit;
            }
        }
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | YUIN Chat</title>
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
    <script src='https://www.google.com/recaptcha/api.js' async defer ></script>
    
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
	<?php
	
	}
	
	?>


	<!-- Breadcrumb section -->
	<div class="site-breadcrumb">
		<div class="container">
			<a href="#"><i class="fa fa-home"></i> Anasayfa</a> <i class="fa fa-angle-right"></i>
			<span>YUIN Chat</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>YUIN Chat</h3>
					<p>Üyeler & Kulüp yetkilileri arasında bilgi paylaşımı ve duyuru sistemi</p>
				</div>
				<form class="comment-form --contact" method="post">
				    <!--<center>
				        <?php
				        
				        if(isset($error)) {
				            
				            ?>
				            <p style="color:red;"><?=$error;?></p>
				            <?php
				        }
				        ?>
				    <div class="col-lg-4">
				        <label for="msg">YUIN Chat'e mesaj gönderin:</label>
						<div style="float: left;">    
						    <img src="<?php
						        
						        if(isset($message['photo'])) {
						            
						            echo $message['photo'];
						        }else{
						            
						            echo getGravatarLink($bilgi['email']);
						        }
						    
						    ?>" alt="Profil">
						    <input type="text" id="msg" name="msg" required>
						</div>
					</div>
					<div class="col-lg-4">
				        <div class="g-recaptcha" data-sitekey="6LcYi7gUAAAAAPar5PlayX7ZDB1g6apxkAh69xVW"></div>
			        </div>
			        <br>
					<div class="col-lg-12">
						<div class="text-center">
							<button class="site-btn">Gönder</button>
						</div>
					</div>
					</center>-->
					<center>
			    <h5>YUIN Chat çok yakında burada!</h5>
			    </center>
				</form>
				<hr>
				<?php
				
				/*foreach($messages as $message) {
				?>
				<blockquote><?=$message['message'];?><hr></blockquote>
				<?php
				}*/
				?>
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