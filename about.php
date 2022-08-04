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
					<!--<iframe width="420" height="315" src="https://www.youtube.com/embed/CibXnZ-XUH8"></iframe>-->
					<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="https://www.instagram.com/p/Cd2u0kwO-5Y/?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"><div style="padding:16px;"> <a href="https://www.instagram.com/p/Cd2u0kwO-5Y/?utm_source=ig_embed&amp;utm_campaign=loading" style=" background:#FFFFFF; line-height:0; padding:0 0; text-align:center; text-decoration:none; width:100%;" target="_blank"> <div style=" display: flex; flex-direction: row; align-items: center;"> <div style="background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 40px; margin-right: 14px; width: 40px;"></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 100px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 60px;"></div></div></div><div style="padding: 19% 0;"></div> <div style="display:block; height:50px; margin:0 auto 12px; width:50px;"><svg width="50px" height="50px" viewBox="0 0 60 60" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-511.000000, -20.000000)" fill="#000000"><g><path d="M556.869,30.41 C554.814,30.41 553.148,32.076 553.148,34.131 C553.148,36.186 554.814,37.852 556.869,37.852 C558.924,37.852 560.59,36.186 560.59,34.131 C560.59,32.076 558.924,30.41 556.869,30.41 M541,60.657 C535.114,60.657 530.342,55.887 530.342,50 C530.342,44.114 535.114,39.342 541,39.342 C546.887,39.342 551.658,44.114 551.658,50 C551.658,55.887 546.887,60.657 541,60.657 M541,33.886 C532.1,33.886 524.886,41.1 524.886,50 C524.886,58.899 532.1,66.113 541,66.113 C549.9,66.113 557.115,58.899 557.115,50 C557.115,41.1 549.9,33.886 541,33.886 M565.378,62.101 C565.244,65.022 564.756,66.606 564.346,67.663 C563.803,69.06 563.154,70.057 562.106,71.106 C561.058,72.155 560.06,72.803 558.662,73.347 C557.607,73.757 556.021,74.244 553.102,74.378 C549.944,74.521 548.997,74.552 541,74.552 C533.003,74.552 532.056,74.521 528.898,74.378 C525.979,74.244 524.393,73.757 523.338,73.347 C521.94,72.803 520.942,72.155 519.894,71.106 C518.846,70.057 518.197,69.06 517.654,67.663 C517.244,66.606 516.755,65.022 516.623,62.101 C516.479,58.943 516.448,57.996 516.448,50 C516.448,42.003 516.479,41.056 516.623,37.899 C516.755,34.978 517.244,33.391 517.654,32.338 C518.197,30.938 518.846,29.942 519.894,28.894 C520.942,27.846 521.94,27.196 523.338,26.654 C524.393,26.244 525.979,25.756 528.898,25.623 C532.057,25.479 533.004,25.448 541,25.448 C548.997,25.448 549.943,25.479 553.102,25.623 C556.021,25.756 557.607,26.244 558.662,26.654 C560.06,27.196 561.058,27.846 562.106,28.894 C563.154,29.942 563.803,30.938 564.346,32.338 C564.756,33.391 565.244,34.978 565.378,37.899 C565.522,41.056 565.552,42.003 565.552,50 C565.552,57.996 565.522,58.943 565.378,62.101 M570.82,37.631 C570.674,34.438 570.167,32.258 569.425,30.349 C568.659,28.377 567.633,26.702 565.965,25.035 C564.297,23.368 562.623,22.342 560.652,21.575 C558.743,20.834 556.562,20.326 553.369,20.18 C550.169,20.033 549.148,20 541,20 C532.853,20 531.831,20.033 528.631,20.18 C525.438,20.326 523.257,20.834 521.349,21.575 C519.376,22.342 517.703,23.368 516.035,25.035 C514.368,26.702 513.342,28.377 512.574,30.349 C511.834,32.258 511.326,34.438 511.181,37.631 C511.035,40.831 511,41.851 511,50 C511,58.147 511.035,59.17 511.181,62.369 C511.326,65.562 511.834,67.743 512.574,69.651 C513.342,71.625 514.368,73.296 516.035,74.965 C517.703,76.634 519.376,77.658 521.349,78.425 C523.257,79.167 525.438,79.673 528.631,79.82 C531.831,79.965 532.853,80.001 541,80.001 C549.148,80.001 550.169,79.965 553.369,79.82 C556.562,79.673 558.743,79.167 560.652,78.425 C562.623,77.658 564.297,76.634 565.965,74.965 C567.633,73.296 568.659,71.625 569.425,69.651 C570.167,67.743 570.674,65.562 570.82,62.369 C570.966,59.17 571,58.147 571,50 C571,41.851 570.966,40.831 570.82,37.631"></path></g></g></g></svg></div><div style="padding-top: 8px;"> <div style=" color:#3897f0; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:550; line-height:18px;">Bu gönderiyi Instagram&#39;da gör</div></div><div style="padding: 12.5% 0;"></div> <div style="display: flex; flex-direction: row; margin-bottom: 14px; align-items: center;"><div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(0px) translateY(7px);"></div> <div style="background-color: #F4F4F4; height: 12.5px; transform: rotate(-45deg) translateX(3px) translateY(1px); width: 12.5px; flex-grow: 0; margin-right: 14px; margin-left: 2px;"></div> <div style="background-color: #F4F4F4; border-radius: 50%; height: 12.5px; width: 12.5px; transform: translateX(9px) translateY(-18px);"></div></div><div style="margin-left: 8px;"> <div style=" background-color: #F4F4F4; border-radius: 50%; flex-grow: 0; height: 20px; width: 20px;"></div> <div style=" width: 0; height: 0; border-top: 2px solid transparent; border-left: 6px solid #f4f4f4; border-bottom: 2px solid transparent; transform: translateX(16px) translateY(-4px) rotate(30deg)"></div></div><div style="margin-left: auto;"> <div style=" width: 0px; border-top: 8px solid #F4F4F4; border-right: 8px solid transparent; transform: translateY(16px);"></div> <div style=" background-color: #F4F4F4; flex-grow: 0; height: 12px; width: 16px; transform: translateY(-4px);"></div> <div style=" width: 0; height: 0; border-top: 8px solid #F4F4F4; border-left: 8px solid transparent; transform: translateY(-4px) translateX(8px);"></div></div></div> <div style="display: flex; flex-direction: column; flex-grow: 1; justify-content: center; margin-bottom: 24px;"> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; margin-bottom: 6px; width: 224px;"></div> <div style=" background-color: #F4F4F4; border-radius: 4px; flex-grow: 0; height: 14px; width: 144px;"></div></div></a><p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;"><a href="https://www.instagram.com/p/Cd2u0kwO-5Y/?utm_source=ig_embed&amp;utm_campaign=loading" style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none;" target="_blank">Yeditepe Bilişim Kulübü (@yuinformatics)&#39;in paylaştığı bir gönderi</a></p></div></blockquote> <script async src="//www.instagram.com/embed.js"></script>
				</div>
			</div>
			
			<h3>Sohbet'e katıl</h3>
			<p>WhatsApp grubumuza katılarak genel kulüp sohbetine katılabilir, bilgi paylaşımında bulunabilir ve kendi ağını oluşturabilirsin</p>
			<?php
			if($login == 1) {
			?>
			<a href="https://chat.whatsapp.com/GKoZH2cTDTNBGA50qW3qRV" target="_blank"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Bilişim Kulübü Whatsapp Grubu 1</button></a><br>
			<a href="https://chat.whatsapp.com/H55W2AcoqvJ69D5PUICDMO" target="_blank"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Bilişim Kulübü Whatsapp Grubu 2</button></a><br>
			<a href="https://chat.whatsapp.com/GhWXIKwwo4z0YJEZ8DWTHH" target="_blank"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Bilişim Kulübü Whatsapp Grubu 3</button></a><br>
			<a href="https://chat.whatsapp.com/ED61SHNRPBQ1VStvpOBtT2" target="_blank"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Bilişim Kulübü Whatsapp Grubu 4</button></a><br>
			<p>Alt gruplarımızın Whatsapp gruplarına katılmak için <a href="subgroup.php"><b>Gruplar</b></a> sayfasına göz atabilirsin.</p>
			<?php
			}else{
			?>
			<a href="login.php?loginToProceed&goto=about.php"><button style="background: #25D366;margin: 5px;" class="site-btn"><i class="fab fa-whatsapp"></i> Whatsapp Gruplarına Katılmak için Giriş Yapınız</button></a>
			<?php
			}
			?>
			<br>
			<h3>Discord?</h3>
			<p>YUInformatics Discord sunucusuna katılıp arkadaşlarınla müzik dinlerken sohbet edebilir, toplantı odalarında birlikte ders çalışabilir ve yeni kişilerle tanışabilirsin</p>
			<?php
			if($login == 1) {
			?>
			<a href="https://discord.gg/kyg2xdk" target="_blank"><button style="background: #7289DA;margin: 5px;" class="site-btn"><i class="fab fa-discord"></i> Bilişim Kulübü Discord Sunucusu</button></a>
			<?php
			}else{
			?>
			<a href="login.php?loginToProceed&goto=about.php"><button style="background: #7289DA;margin: 5px;" class="site-btn"><i class="fab fa-discord"></i> Discord Sunucusuna Katılmak için Giriş Yapınız</button></a>
			<?php
			}
			?>
			<h3>Ücretsiz WiFi</h3>
			<p>Kulüp odamızda ücretsiz WiFi mevcuttur. Ders çalışmak, müzik dinlemek ve oyun oynamak için kulüp odamızı istediğin zaman kullanabilirsin.</p>
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