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

// Fakulte listesini getir
if($stmt = $pdo->prepare("SELECT * FROM fakulteler GROUP BY faculty DESC")) {
        
    if($stmt->execute()) {
        
        $fakulteler = $stmt->fetchAll();
    }
}

// Kayıt başlangıcı

$error = "";
$name = "";
$email = $phonenum = $passverifi = $pass = $department = $faculty = $user = $surname = $name;

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $user = trim($_POST['user']);
    $faculty = trim($_POST['faculty']);
    $department = trim($_POST['department']);
    $pass = trim($_POST['pass']);
    $passverifi = trim($_POST['passverifi']);
    $phonenum = trim($_POST['phonenum']);
    $email = trim($_POST['email']);
    
    if(empty($name)) {
        
        $error .= "Lütfen adınızı giriniz." . PHP_EOL;
    }
    
    if(empty($surname)) {
        
        $error .= "Lütfen soyadınızı giriniz." . PHP_EOL;
    }
    
    $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
    $surname = filter_var($surname, FILTER_SANITIZE_SPECIAL_CHARS);
    
    if(empty($user)) {
        
        $error .= "Lütfen öğrenci numaranızı giriniz." . PHP_EOL;
    }
    
    if(empty($faculty)) {
        
        $error .= "Lütfen fakültenizi seçiniz." . PHP_EOL;
    }
    
    if(empty($department)) {
        
        $error .= "Lütfen bölümünüzü giriniz." . PHP_EOL;
    }
    
    $department = filter_var($department, FILTER_SANITIZE_SPECIAL_CHARS);
    
    if(empty($pass)) {
        
        $error .= "Lütfen şifrenizi belirleyiniz." . PHP_EOL;
    }
    
    if(empty($passverifi)) {
        
        $error .= "Lütfen belirlediğiniz şifreyi doğrulayınız." . PHP_EOL;
    }
    
    if(empty($phonenum)) {
        
        $error .= "Lütfen telefon numaranızı giriniz." . PHP_EOL;
    }else{
        
        if($phonenum[0] != '0') {
            
            $error .= "Lütfen telefon numaranızı 05XXXXXXXXX formatında giriniz. <b>IF YOU ARE A FOREIGN STUDENT, YOU CAN INPUT A RANDOM PHONE NUMBER THAT FITS THE CRITERIA (Ex: 05444444444) MENTIONED.</b>" . PHP_EOL;
        }
    }
    
    $phonenum = filter_var($phonenum, FILTER_SANITIZE_SPECIAL_CHARS);
    
    if(empty($email)) {
        
        $error .= "Lütfen e-posta adresinizi giriniz." . PHP_EOL;
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $error .= "E-posta adresiniz geçersiz!" . PHP_EOL;
    }
    
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $phonenum = filter_var($phonenum, FILTER_SANITIZE_SPECIAL_CHARS);
    
    if($user[0] != '2' || strlen($user) < 11) {
        
        $error .= "Öğrenci numaranızı yanlış yazdınız." . PHP_EOL;
    }
    
    if($stmt = $pdo->prepare("SELECT uid FROM users WHERE user = :user")) {
        
        $stmt->bindParam(':user', $user, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            if($stmt->rowCount() > 0) {
                
                $error .= "Bu kullanıcı sistemde zaten kayıtlı. Lütfen giriş yapınız." . PHP_EOL;
            }
        }
        
        unset($stmt);
    }
    
    if($pass != $passverifi) {
        
        $error .= "Şifre ve şifre doğrulama kısmına girdiğiniz şifreler uyuşmuyor, lütfen tekrar deneyin." . PHP_EOL;
    }
    
    if(!gRecaptchaVerify($_POST['g-recaptcha-response'])) {
        
        $error .= "Captcha doğrulaması başarısız oldu! lütfen tekrar dene." . PHP_EOL;
    }
    
    if(empty($error)) {
        
        if($stmt = $pdo->prepare("INSERT into users (user,pass,name,surname,faculty,department,permlevel,email,phonenum) VALUES (:user, :pass, :name, :surname, :faculty, :department, :permlevel, :email, :phonenum)")) {
            
            $stmt->bindParam(':user', $user, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $hpass, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            $stmt->bindParam(':faculty', $faculty, PDO::PARAM_STR);
            $stmt->bindParam(':department', $department, PDO::PARAM_STR);
            $stmt->bindParam(':permlevel', $permlevel, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':phonenum', $phonenum, PDO::PARAM_STR);
            
            $hpass = password_hash($pass, PASSWORD_DEFAULT);
            $permlevel = 0;
            
            if($stmt->execute()) {
                
                unset($stmt);
                unset($pdo);
                
                header('Location: login.php?signup');
                exit;
            }else{
                $error .= "Yerel bir sistem hatası meydana geldi! (2) lütfen daha sonra tekrar deneyin. Eğer sorun düzelmez ise bu durumu yönetim kadrosundaki herhangi birine bildirin." . PHP_EOL;
            }
        }else{
            
            $error .= "Yerel bir sistem hatası meydana geldi! (1) lütfen daha sonra tekrar deneyin. Eğer sorun düzelmez ise bu durumu yönetim kadrosundaki herhangi birine bildirin." . PHP_EOL;
        }
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Aramıza katıl</title>
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
				    
				    <li>Tekrardan hoşgeldiniz Sayın <?=$bilgi['name'] . ' ' . $bilgi['surname'];?></li> 
				    
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


	<!-- Breadcrumb section -->
	<div class="site-breadcrumb">
		<div class="container">
			<a href="#"><i class="fa fa-home"></i> Ana Sayfa</a> <i class="fa fa-angle-right"></i>
			<span>Aramıza katıl</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->
	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>Aramıza Katıl</h3>
					<h5>Her şey senin için. Bilişim Kulübü'nün sunduğu tüm imkanlardan yararlanmak için hemen aramıza katıl :)</h5>
					<br>
					<p style="margin:20px;">OBS ile giriş yaparak kulübümüze kaydolabilirsiniz. Bu durumda YUIN Club sistemimiz kulübümüze kaydınız için gereken bilgileri OBS üzerinden otomatik olarak çekecek ve kaydınız birkaç saniyede tamamlanacaktır. Eğer OBS şifrenizi paylaşmak istemezseniz kişisel bilgilerinizi elle girerek manuel olarak kaydolabilmektesiniz.</p>
					<!--<a href="fastjoin.php">--><button class="site-btn" onclick="alert('OBS ile hızlı giriş hizmetini sağlayabilmemiz için Yeditepe BiM ile ortak çalışmalar devam etmektedir.');">OBS ile giriş yaparak hızlı katıl</button><!--</a>-->
					<a href="#form"><button class="site-btn">Manuel katılım sistemi</button></a>
				</div>
				<div class="contact-form spad pb-0">
				<div id="form" class="section-title text-center">
				    <h3>Manuel katılım formu</h3>
				    <p>Hızlı kayıt sistemini kullanmak istemiyorsan buradan kulübümüze kaydolabilirsin.</p>
				</div>
				<form class="comment-form --contact" method="post">
					<center>
					    <p style="color:red;margin:20px;"><?=$error;?></p>
						<div class="col-lg-4">
							<input type="text" name="name" placeholder="Adınız" required>
						</div>
						<div class="col-lg-4">
							<input type="text" name="surname" placeholder="Soyadınız" required>
						</div>
						<div class="col-lg-4">
							<input type="text" name="user" placeholder="Öğrenci Numaranız" required>
						</div>
						<div class="col-lg-4">
						    <label for="fak">Fakülteniz</label>
							<select id="fak" name="faculty">
                                <option value="">___ FAKÜLTENİZİ SEÇİNİZ ___</option>
                                <?php
                                
                                // PHP Başlangıç
                                foreach($fakulteler as $fakulte) {
                                    
                                    echo '<option value="' . $fakulte['faculty'] . '">' . $fakulte['faculty'] . '</option>' . PHP_EOL;
                                }
                                
                                // PHP Bitiş
                                ?>
                            </select>
						</div>
						<div class="col-lg-4">
							<input type="text" name="department" placeholder="Bölümünüz" required>
						</div>
						<div class="col-lg-4">
							<input type="password" name="pass" placeholder="Şifre belirleyin" required>
						</div>
						<div class="col-lg-4">
							<input type="password" name="passverifi" placeholder="Şifrenizi doğrulayın" required>
						</div>
						<div class="col-lg-4">
							<input type="text" name="phonenum" placeholder="Telefon numaranız" required>
						</div>
						<div class="col-lg-4">
							<input type="email" name="email" placeholder="E-posta adresiniz" required>
						</div>
						<div class="col-lg-4">
						    <center><div class="g-recaptcha" data-sitekey="6LfwnO8UAAAAANhxO1zsoDnlgAu8_KK0PnB4AqmW"></div></center>
					    </div>
						
						<div class="col-lg-12">
							<div class="text-center">
							    <br><p><i>Alttaki Aramıza Katıl butonuna tıklayarak üstte belirttiğim tüm kişisel bilgilerimi Yeditepe Üniversitesi Bilişim Kulübü (YUINFORMATICS) ile paylaşmayı kabul ediyor ve YUINFORMATICS'e üye kaldığım sürece bu bilgilerin YUINFORMATICS tarafından güvenle saklanıp, asla üçüncü taraflarla paylaşılmayacağını anlıyor ve kabul ediyorum.</i></p>
								<button style="margin:20px;" class="site-btn">Aramıza Katıl</button>
							</div>
						</div>
					</center>
				</form>
			    </div>
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