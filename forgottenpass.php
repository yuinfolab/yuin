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

// Korunan sabitleri getir
require_once "/home/yuinyeditepe/public_html/protected/protected_constants.php";

// Gizli anahtarı üreten fonksiyon
function gizliAnahtarOlustur(&$salts, $email) {
    
    $todaySeed = date('d-m-Y');
    $seed = strtotime($todaySeed);
    fisherYatesShuffle($salts, $seed);
    $salts = implode('', $salts);
    $key = hash('SHA256', $email . $salts);
    return $key;
}

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
$ip = getUserIP(); // temp

// Testing
if($ip != '94.54.98.240') {
    
    echo $ip;
    exit;
}

$key = '';
if(isset($_GET['key']) && !empty($_GET['key'])) {
    
    $key = trim($_GET['key']);
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $error = '';
    $success = '';
    if(isset($_POST['email']) && !empty($_POST['email']) && filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        
        $email = trim($_POST['email']);
    }else{
        
        $error .= "Geçersiz şifre sıfırlama anahtarı veya böyle bir eposta mevcut değil. Lütfen tekrar deneyiniz." . PHP_EOL;
    }
    
    // Girilen eposta sistem üzerinde kayıtlı mı kontrol et. Eğer mevcutsa da ziyaretçiye çaktırmadan aynı hataları döndür.
    if($stmt = $pdo->prepare('SELECT COUNT(email) AS emailSayi FROM users WHERE email = :email')) {
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $emailSayi = $stmt->fetch();
            $emailSayi = $emailSayi['emailSayi'];
        }else{
            
            $error .= "Sorgu çalıştırılırken bir hata meydana geldi lütfen daha sonra tekrar deneyiniz." . PHP_EOL;
        }
    }else{
        
        $error .= "Sorgu çalıştırılırken bir hata meydana geldi lütfen daha sonra tekrar deneyiniz." . PHP_EOL;
    }
    
    $salts = [YUIN_SALT1, YUIN_SALT2, YUIN_SALT3, YUIN_SALT4, YUIN_SALT5, YUIN_SALT6, YUIN_SALT7, YUIN_SALT8, YUIN_SALT9, YUIN_SALT10];
    $generatedSecretKey = gizliAnahtarOlustur($salts, $email);
    
    // Aşama başlangıcı
    if(isset($email) && isset($_POST['key']) && isset($_POST['newpass']) && isset($_POST['newpassverifi']) && !empty($_POST['key']) && !empty($_POST['newpass']) && !empty($_POST['newpassverifi']) && empty($error)) {
        // 2. Aşama
        
        $key = trim($_POST['key']);
        $newpass = trim($_POST['newpass']);
        $newpassverifi = trim($_POST['newpassverifi']);
        
        if($generatedSecretKey != $key || $emailSayi < 1) {
            
            $error .= "Geçersiz şifre sıfırlama anahtarı veya böyle bir eposta mevcut değil. Lütfen tekrar deneyiniz." . PHP_EOL;
        }else{
            
            if($newpass != $newpassverifi) {
                
                $error .= "Girilen şifreler birbirleriyle uyuşmuyor! Lütfen tekrar deneyiniz." . PHP_EOL;
            }else{
                
                $hpass = password_hash($newpass, PASSWORD_DEFAULT);
                if($stmt = $pdo->prepare('UPDATE users SET pass = :newpass WHERE email = :email')) {
                    
                    $stmt->bindParam(':newpass', $hpass, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    if($stmt->execute()) {
                        
                        // Şifre bu aşamada sıfırlandı
                        
                        unset($stmt);
                        unset($pdo);
                        header('Location: login.php?sifreSifirlandi');
                        exit;
                    }else{
                        
                        $error .= "Sorgu çalıştırılırken bir hata meydana geldi lütfen daha sonra tekrar deneyiniz." . PHP_EOL;
                    }
                }else{
                    
                    $error .= "Sorgu çalıştırılırken bir hata meydana geldi lütfen daha sonra tekrar deneyiniz." . PHP_EOL;
                }
            }
        }
    }else if(isset($email) && empty($error)) {
        
        $title = 'YUIN Club Şifre Sıfırlama';
        $content = <<<EMAIL
Selamlar!
<br><br>
yuin.yeditepe.edu.tr üzerinde kayıtlı olan hesabınız için şifrenizin sıfırlanmasını talep ettiniz. Hesabınız için yeni şifre belirlemek için alttaki linke tıklayınız:
<a href="https://yuin.yeditepe.edu.tr/forgottenpass.php?key=$key">https://yuin.yeditepe.edu.tr/forgottenpass.php?key=$generatedSecretKey</a>
<br><hr><br>
Yeditepe Üniversitesi Bilişim Kulübü
<br><br>
https://yuin.yeditepe.edu.tr
https://www.instagram.com/YuInformatics
https://www.linkedin.com/in/YuBilisimKulubu
https://www.twitter.com/YuInformatics</i>
EMAIL;
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.=w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns=3D"http://www.w3.org/1999/xhtml" lang=3D"en"><head><meta charset="utf-8" lang="tr"><title>' . $title . '</title></head><body>' . $content . '</body></html>';
        if($emailSayi > 0) {
            
            $s = sendEmail($email, $content, $title);
            if(!$s) {
                
                echo $email . ' Eposta gonderilemedi! Lütfen YUInformatics yönetim kadrosu ile iletisime gecin ve bu hatayi onlara iletin. Eposta sifresi degistirilmis olabilir.';
                exit;
            }
            $success = 'yes';
        }
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Şifremi Unuttum</title>
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
	
	<?=/* En üst barı göster */ file_get_contents('tmpller/headerEnUst.tmpl');?>
	
	<!-- Header section  -->
	<nav class="nav-section">
		<div class="container">
			<div class="nav-right">
				<?php
				
				    // PHP Başlangıç
				    
				    if($login == 0) {
				        
				    ?>
				    <li style="list-style-type: none;"><a href="login.php"><i class="fas fa-sign-in-alt"></i> Giriş yap</a></li>
				    <?php
				    
				    }else{
				    ?>
				    
				    <li style="list-style-type: none;"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış yap</a></li>
				    
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
			    <li style="list-style-type: none;"><a href=""> Tekrardan hoşgeldiniz sayın <?=$bilgi['name'] . ' ' . $bilgi['surname'];?></a></li>
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
			<a href="#"><i class="fa fa-home"></i> Ana Sayfa</a> <i class="fa fa-angle-right"></i>
			<span>Şifremi Unuttum</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>Şifremi Unuttum</h3>
					<p>Şifrenizi buradan sıfırlayabilirsiniz.</p>
				</div>
				
				<form method="post" class="comment-form --contact">
				    <center>
				    <?php
				    if(empty($success)) {
				    if(!empty($key)) {
				    ?>
				    <div class="col-lg-4">
						<input type="text" name="key" placeholder="Şifre sıfırlama anahtarı" value="<?=$key;?>">
					</div>
					<div class="col-lg-4">
						<input type="text" name="newpass" placeholder="Yeni şifreniz" required>
					</div>
					<div class="col-lg-4">
						<input type="text" name="newpassverifi" placeholder="Tekrardan yeni şifreniz" required>
					</div>
					<?php
				    }
				    ?>
				    <div class="col-lg-4">
						<input type="text" name="email" placeholder="Eposta adresiniz" required>
					</div>
					<div class="col-lg-12">
					    <p>Alttaki butona tıkladığınızda, eğer sistemde belirtilen eposta hesabını içeren bir kayıt mevcut ise, ilgili eposta adresine, <b><?=YUIN_SMTP_ACCT;?></b> adresi üzerinden bir link gönderilecek. Linke tıkladığınızda bu sayfaya geri yönlendirileceksiniz ve yeni şifrenizi belirleyebileceksiniz. Eğer gönderilen epostayı kendi eposta gelen kutunuzda bulamazsanız Spam veya Gereksiz bölümlerini de kontrol ediniz. Eğer bu kısımlarda da bulamazsanız herhangi bir Yönetim Kadrosu üyesi ile iletişime geçip destek isteyebilirsiniz.</p>
					    <button style="margin:20px;" class="site-btn">Şifre sıfırlama anahtarını gönder</button>
					</div>
					<?php
				    }else{
					?>
					<h3 style="color: green;">Eğer böyle bir hesap mevcut ise, şfire sıfırlama epostası başarıyla gönderildi. <b>Eposta adresinizde Gelen kutunuzu ve Spam / Gereksiz kutularını kontrol etmeyi unutmayınız.</b></h3>
					<?php
				    }
				    ?>
					</center>
				</form>
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
	
</body>
</html>