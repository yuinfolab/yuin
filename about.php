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
}

/*

13.05.2020 İTİBARİYLE contact.php SAYFASINA TAŞINDI.

if($stmt = $pdo->prepare("SELECT * FROM adminInfo INNER JOIN users ON adminInfo.id=users.uid")) {
    
    if($stmt->execute()) {
        
        $admins = $stmt->fetchAll();
    }
}

usort($admins, function($a, $b) {
    
    return $a['priority'] - $b['priority'];
});

*/

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Hakkımızda</title>
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
	<?php
	
	}
	
	?>

	<!-- Breadcrumb section -->
	<div class="site-breadcrumb">
		<div class="container">
			<a href="#"><i class="fa fa-home"></i> Ana Sayfa</a> <i class="fa fa-angle-right"></i>
			<span>Hakkımızda</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- About section -->
	<section class="about-section spad pt-0">
		<div class="container">
			<div class="section-title text-center">
				<h3>BİLİŞİM KULÜBÜ (YUIN)</h3>
				<p>Bilişim'in her alanıyla ilgilenen herkese hitap eden kulübümüz YUInformatics'e hoşgeldin!</p>
			</div>
			<div class="row">
				<div class="col-lg-6 about-text">
					<h5>Bilişim Kulübü ne yapıyor?</h5>
					<p>Günümüzde gelişen teknolojiyi ve şirketlerin teknolojiyi bilinçli ve yerinde kullanan çalışan arayışının artışını gözlemleyen Bilişim Kulübü,</p>
					<ul class="about-list">
					    <li><i class="fas fa-check"></i> Üyeleriyle birlikte güncel teknoloji trendlerini takip etmek için çeşitli konularda konferanslar düzenler.</li>
					    <li><i class="fas fa-check"></i> Organize ettiği etkinlikler ile üyelerinin birbiriyle etkileşimlerini artırmayı ve onları profesyonel çalışma hayatına hazırlamayı hedefler.</li>
					</ul>
					<h5 class="pt-4">Etkinlik kategorilerimiz</h5>
					<ul class="about-list">
						<li><i class="fas fa-check"></i> <b>INFORMATIC TALKS</b> Düzenli olarak belirli aralıklarla bilişim sektöründen konuşmacılar ağırladığımız ve öğrenciler ile buluşturduğumuz konferans türündeki etkinliğimizdir.</li>
						<li><i class="fas fa-check"></i> <b>INDUSTRY NIGHT</b> Yılda bir kez öğrenciler ile sektördeki mezunları buluşturmayı hedefleyen bir organizasyondur. Bu organizasyonda katılımcı öğrenciler profesyonel hayat ile ilgili fikir sahibi olur ve merak ettikleri sorulara cevap bulabilme fırsatını yakalarlar. Kokteyl tarzında düzenlenen etkinliğimiz Yeditepe Sofrası’nda yapılmaktadır.</li>
						<li><i class="fas fa-check"></i> <b>SAHA GEZİLERİ</b> Kulübümüz ve sektördeki çeşitli firmalar ile işbirliği yapılarak düzenlenen teknik gezilerdir. Bu geziler sayesinde öğrenciler profesyonel çalışma ortamlarını yerinde gözlemleme fırsatı yakalar.</li>
						<li><i class="fas fa-check"></i> <b>YÖNETİM BİLİŞİM ZİRVESİ</b> Bilişim Kulübü olarak 2017 ve 2018 yıllarında
ulusal olarak düzenlenen IV. ve V. Yönetim
Bilişim Zirvesi’ne katılım gerçekleştirdik. İki gün
boyunca süren konferanslarda çeşitli şehirlerden
gelen akademisyenleri, girişimcileri ve sektörün
önde gelen firmalarını dinleme fırsatı yakaladık.
V. zirve sonuna geldiğimizde ise VI. Yönetim
Bilişim Zirvesi’ni okulumuzda düzenleme şansını
elde ettik. Girişimcilik Kulübü ile iş birliği
içerisinde bir sene çalışmamızın ardından 13-14
Nisan 2019 tarihlerinde zirveyi Yeditepe
Üniversitesi’nde düzenledik.</li>
					</ul>
				</div>
				<div class="col-lg-6 pt-5 pt-lg-0">
					<iframe width="420" height="315" src="https://www.youtube.com/embed/CibXnZ-XUH8"></iframe>
				</div>
			</div>
			
			<h3>Sohbet'e katıl</h3>
			<p>WhatsApp grubumuza katılarak genel kulüp sohbetine katılabilir, bilgi paylaşımında bulunabilir ve kendi ağını oluşturabilirsin</p>
			<?php
			if($login == 1) {
			?>
			<a href="https://chat.whatsapp.com/GKoZH2cTDTNBGA50qW3qRV"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Bilişim Kulübü Whatsapp Grubu</button></a>
			<?php
			}else{
			?>
			<a href="login.php?loginToProceed"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Bilişim Kulübü Whatsapp Grubu</button></a>
			<?php
			}
			?>
			<br>
			<h3>Discord?</h3>
			<p>YUInformatics Discord sunucusuna katılıp arkadaşlarınla müzik dinlerken sohbet edebilir, toplantı odalarında birlikte ders çalışabilir ve yeni kişilerle tanışabilirsin</p>
			<?php
			if($login == 1) {
			?>
			<a href="https://discord.gg/kyg2xdk"><button style="background: #7289DA;margin: 5px;" class="site-btn"><i class="fab fa-discord"></i> Bilişim Kulübü Discord Sunucusu</button></a>
			<?php
			}else{
			?>
			<a href="login.php?loginToProceed"><button style="background: #7289DA;margin: 5px;" class="site-btn"><i class="fab fa-discord"></i> Bilişim Kulübü Discord Sunucusu</button></a>
			<?php
			}
			?>
			<h3>Ücretsiz WiFi</h3>
			<p>Kulüp odamızda ücretsiz WiFi mevcuttur. 100 Mbps hızında internette gezinmek için Kulüp odamıza uğra.</p>
			<p>WiFi Adı: <b>yuin.yeditepe.edu.tr</b></p>
			<p>WiFi'yi kullanmanız için captive portal'dan giriş yapmanız gerekmektedir. WiFi ağımızın şifresi yoktur. Ağımıza bağlantı kurduktan sonra Wifi login ekranı birkaç saniye içerisinde açılacaktır. Eğer login ekranı Windows cihazınızda gelmiyorsa tarayıcınızı kapatıp yeniden açmayı veya bir siteye girmeyi deneyebilirsiniz. <br><b>Wifi ağına bağlanırken sorun yaşamanız durumunda</b><br><a href="https://support.apple.com/tr-tr/HT204497">Apple cihazları için login olmak için bu yörüngeleri takip edebilirsiniz.</a><br><a href="https://www.auslogics.com/en/articles/fix-public-wifi-login-page-not-showing-up/">Windows cihazından login olmak için bu yörüngeleri takip edebilirsiniz.</a><br></p>
			<h3>Diğer platformlarda YUINformatics</h3>
			<p>Bilişim Kulübü'nün aktif olduğu tüm platformları websitemizin en alt kısmında görebilirsin.</p>
			
		</div>
	</section>
	<!-- About section end-->
	
	<!-- Newsletter section
	<section class="newsletter-section">
		<div class="container">
			<div class="row">
				<div class="col-md-5 col-lg-7">
					<div class="section-title mb-md-0">
					<h3>NEWSLETTER</h3>
					<p>Subscribe and get the latest news and useful tips, advice and best offer.</p>
				</div>
				</div>
				<div class="col-md-7 col-lg-5">
					<form class="newsletter">
						<input type="text" placeholder="Enter your email">
						<button class="site-btn">SUBSCRIBE</button>
					</form>
				</div>
			</div>
		</div>
	</section>
	<!-- Newsletter section end -->	

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