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
    
    header('Location: index.php');
    exit;
}

define('YUIN',1);

// Veritabanınla bağlantı kur ve basit ayarları uygula (mesela debugging)
require_once "/home/yuinyeditepe/public_html/backend/connect.php";

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";

// Aktif üye sayısını enteresan bir şekilde hesapla
// Çok kötü olduğunu biliyorum ama iş görüyor. Daha iyisini yapamıyorsanız ellemeyin.

$sessionsdir = ini_get('session.save_path');
$sessionlife = ini_get('session.gc_maxlifetime');

$m30b = time() - $sessionlife;

$sessions = scandir(ini_get("session.save_path"));

unset($sessions[0]); //.
unset($sessions[1]); //..

$suanaktif = 0;
foreach($sessions as $session) {
    
    $fc = filemtime($sessionsdir . '/' . $session);
    if($fc >= $m30b) {
        
        $suanaktif++;
    }
}

// Kayıtlı üye sayısını getir
$kayitli = false;
if($stmt = $pdo->prepare("SELECT COUNT(*) FROM users")) {
    
    if($stmt->execute()) {
        
        if($kayitli = $stmt->fetch()) {
            
            $kayitli = (int)@$kayitli['COUNT(*)'];
        }
    }
}

// Hesaplayamadık sanki
if(!is_numeric($kayitli)) {
    
    $kayitli = '<b>Hesaplanırken hata meydana geldi!</b>';
}


$error = "";
$user = "";
$password = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user = trim($_POST['user']);
    $pass = trim($_POST['pass']);
    
    if(empty($user)) {
        
        $error .= "Lütfen öğrenci numaranızı ya da kullanıcı adınızı (varsa) giriniz." . PHP_EOL;
    }
    
    if(empty($pass)) {
        
        $error .= "Lütfen şifrenizi giriniz." . PHP_EOL;
    }
    
    if(empty($error)) {
        
        if($stmt = $pdo->prepare("SELECT uid, status, user, pass FROM users WHERE user = :user")) {
            
            // PDO parametrelerini ata
            $stmt->bindParam(":user", $user, PDO::PARAM_STR);
            if($stmt->execute()) {
                
                if($stmt->rowCount() == 1 && $row = $stmt->fetch()) {
                    
                    $uid = $row['uid'];
                    $user = $row['user'];
                    $hpass = $row['pass'];
                    
                    if($row['status'] == 0) {
                        
                        unset($row);
                        unset($stmt);
                        unset($pdo);
                        
                        header('Location: login.php?suspended');
                        exit;
                    }
                    
                    if(password_verify($pass, $hpass)) {
                        
                        // Şifre doğru, login başarılı.
                        
                        $_SESSION['login'] = 1;
                        $_SESSION['uid'] = $uid;
                        $_SESSION['user'] = $user;
                        $_SESSION['ip'] = getUserIP();
                        
                        unset($stmt);
                        unset($pdo);
                        
                        header('Location: index.php');
                        exit;
                    }else{
                        
                        $error .= "Eksik veya hatalı şifre girdiniz, tekrar deneyin." . PHP_EOL;
                    }
                }else{
                    
                    $error .= "Böyle bir hesap bulunamadı! kaydolmak isterseniz navigasyondan kaydolabilirsiniz." . PHP_EOL;
                }
            }else{
                
                $error .= "Bir hata meydana geldi. Lütfen daha sonra tekrar deneyin. Eğer bu sorun bir süre daha devam ederse yönetim kurulundan birine haber verin." . PHP_EOL;
            }
        }
    }
}

unset($stmt);
unset($pdo);

if(isset($_GET['kurumdisi'])) {
    
    $kurumdisi = true;
}else{
    
    $kurumdisi = false;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | YUIN Club'a giriş yap</title>
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
				<li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş yap</a></li>
			</div>
			<ul class="main-menu">
				<?=/* Ziyaretçi navigasyon barını göster */ file_get_contents('tmpller/ziyaretciNavbar.tmpl');?>
			</ul>
		</div>
	</nav>
	<!-- Header section end -->

	<!-- Breadcrumb section -->
	<div class="site-breadcrumb">
		<div class="container">
			<a href="#"><i class="fa fa-home"></i> Ana Sayfa</a> <i class="fa fa-angle-right"></i>
			<span>YUIN Club</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- About section -->
	<section class="about-section spad pt-0">
		<div class="container">
			<div class="section-title text-center">
				<h3>YUIN Club'a giriş yap</h3>
				<p>Kulübümüzle ilgili tüm işlemlerinizi buradan gerçekleştirebilirsiniz<br><br><i class="fas fa-signal"></i> <i>Anlık aktif üye sayısı: <?=$suanaktif;?></i><br><i class="fas fa-users"></i> <i>Sitemize kayıtlı toplam üye sayısı: <?=$kayitli;?></i></p>
				<br>
				<?php
			    
			    if(isset($_GET['loginToProceed'])) {
			        
			        ?>
			        <div class="alert alert-warning">
                        
                        <strong><i class="fas fa-exclamation-triangle"></i> Uyarı!</strong> Devam etmek için lütfen giriş yapın.
                    </div>
			        <?php
			    }
			    
			    if(isset($_GET['ipc'])) {
			        
			        ?>
			        <div class="alert alert-danger">
                        
                        <strong><i class="fas fa-exclamation-triangle"></i> Uyarı!</strong> IP Adresiniz değiştiği için güvenlik nedeniyle sistemden çıkartıldınız. Lütfen yeniden giriş yapın.
                    </div>
			        <?php
			    }
			    
			    ?>
				<p style="color:red;margin:20px;"><?=$error;?></p>
			</div>
			
			<form class="comment-form --contact" method="post">
				
				<center>
				    <div class="col-lg-6">
					    <input id="loginparam" type="text" name="user" placeholder="<?php if(!$kurumdisi): echo 'Öğrenci numaranız'; else: echo 'Telefon numaranız'; endif; ?>" required>
				    </div>
				    <div class="col-lg-6">
					    <input type="password" name="pass" placeholder="YUIN Club sifreniz" autocomplete="off" required>
				    </div>
				    <div class="col-lg-6">
					    <label for="ki">Yeditepe Üniversitesi öğrencisiyim <b>(Kurum içi giriş)</b></label><br>
					    <input type="radio" onclick="document.getElementById('loginparam').placeholder = 'Öğrenci numaranız';" id="ki" name="giristipi" value="ki" <?php if(!$kurumdisi): echo 'checked'; endif; ?>>
                        
                        <label for="kd">Yeditepe Üniversitesi öğrencisi değilim <b>(Kurum dışı)</b></label><br>
                        <input type="radio" onclick="document.getElementById('loginparam').placeholder = 'Telefon numaranız';" id="kd" name="giristipi" value="kd" <?php if($kurumdisi): echo 'checked'; endif; ?>>
				    </div>
				</center>
				<div class="col-lg-12">
					<div class="text-center">
					    <p>Kulübümüze üye değilmisin? <a href="join.php">Buraya tıklayarak</a> aramıza katılabilirsin</p>
						<button class="site-btn">Giriş yap</button>
					</div>
				</div>
			</form>
		</div>
	</section>
	<!-- About section end-->
	
	<?=/* Footer kısmını göster */ file_get_contents('tmpller/footer.tmpl');?>
    
	<!--====== Javascripts & Jquery ======-->
	<script src="js/jquery-3.2.1.min.js"></script>
	<script src="js/owl.carousel.min.js"></script>
	<script src="js/jquery.countdown.js"></script>
	<script src="js/masonry.pkgd.min.js"></script>
	<script src="js/magnific-popup.min.js"></script>
	<script src="js/main.js"></script>
	
</body>
</html>