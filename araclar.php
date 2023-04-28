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

$arac = '';
if(isset($_GET['arac']) && !empty($_GET['arac'])) {
    
    $arac = trim($_GET['arac']);
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Araçlar</title>
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
			<span>Araçlar</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			<div class="section-title text-center">
				<h3>Araçlar</h3>
				<p>Kulübümüzün IT ekibi tarafından sizlere sağlanan bilgi kaynaklarına buradan erişebilirsiniz</p>
				<br>
				<?php
				if(empty($arac)) {
				?>
				<table>
				    <tr>
				        <td><b>Araç ismi</b></td>
				        <td><b>Araç açıklaması</b></td>
				        <td><b>Aracı görüntüle</b></td>
				    </tr>
				    <tr>
				        <td>Ortalama Hesaplama</td>
				        <td>Dönem ortalamanızı bu aracı kullanarak kolaylıkla hesaplayabilirsiniz.</td>
				        <td><a href="?arac=ortalama"><i class="fas fa-arrow-alt-circle-right"></i></a></td>
				    </tr>
				    <!--<tr>
				        <td>Yeditepe Kontenjan</td>
				        <td>Yeditepe Üniversitesi'ne giren öğrenci kontenjan bilgisini getirir</td>
				        <td><a href="?arac=kontenjan" class="site-btn"><i class="fas fa-arrow-alt-circle-right"></i></a></td>
				    </tr>
				    <tr>
				        <td>Yeditepe Harita</td>
				        <td>Bilgi İşlem Koordinatörlüğü tarafından sağlanan Yeditepe Üniversitesi 26 Ağustos Kampüsü haritasını gösterir</td>
				        <td><a href="?arac=harita"><i class="fas fa-arrow-alt-circle-right"></i></a></td>
				    </tr>-->
				    <tr>
				        <td>Bağlantı Bilgilerim</td>
				        <td>Tarayıcınızdan bize gelen veriyi gösterir. (IP Adresiniz, Tarayıcı bilgileriniz vb.)</td>
				        <td><a href="?arac=baglantim"><i class="fas fa-arrow-alt-circle-right"></i></a></td>
				    </tr>
				</table>
				<?php
				}else if($arac == 'ortalama'){
				    // Ortalama hesaplama
				    ?>
				    <div id="hn-universite-not-ortalamasi-widget"></div><script src="https://e.hesaplama.net/universite-not-ortalamasi.do?bgcolor=0243AB&tcolor=FFFFFF&hcolor=3B8CEE&rcolor=EEEEEE&tsize=n&tfamily=n&btype=c&bsize=1px&bcolor=EEEEEE" type="text/javascript"></script>
				    <?php
				}else if($arac == 'kontenjan') {
				    // Kontenjan
				    ?>
				    
				    <?php
				}else if($arac == 'harita') {
				    // Harita
				    ?>
				    <h4>26 Ağustos Yerleşkesi Haritası</h4>
				    <center>
				        <iframe style="width:85%;height:720px;" src="https://konum.yeditepe.edu.tr/" frameborder="0" allowfullscreen></iframe>
				    </center>
				    <?php
				}else if($arac == 'baglantim') {
				    // Bağlantım
				    
				    $ip = getUserIP();
				    ?>
				    <h4>Tarayıcınızın bize gönderdiği bilgiler</h4>
				    <table>
				        <tr>
				            <td><b>Değer</b></td>
				            <td><b>İçerik</b></td>
				        </tr>
				        <tr>
				            <td>IP Adresiniz</td>
				            <td><?=$ip;?></td>
				        </tr>
				        <?php
				        
				        foreach($_SERVER as $HeaderKey => $HeaderValue) {
				        
    				        if(strpos('i' . $HeaderKey, 'HTTP') == false) {
    				            
    				            continue;
    				        }
    				        $HeaderKey = str_replace('HTTP_', null, $HeaderKey);
    				        ?>
    				        <tr>
    				            <td><?=$HeaderKey;?></td>
    				            <td><?=$HeaderValue;?></td>
    				        </tr>
    				        <?php
    				    }
    				    ?>
    				    </table>
    				    <h4>Session Deposu İçeriği</h4>
    				    <table>
    				        <tr>
    				            <td><b>Değer</b></td>
    				            <td><b>İçerik</b></td>
    				        </tr>
    				    <?php
				        
				        foreach($_SESSION as $HeaderKey => $HeaderValue) {
				        
    				        ?>
    				        <tr>
    				            <td><?=$HeaderKey;?></td>
    				            <td><?=$HeaderValue;?></td>
    				        </tr>
    				        <?php
    				    }
    				    ?>
    				    </table>
    				    <?php
				}else{
				    // Araç bulunamadı
				    ?>
				    <center>
				        <h3>Araç Bulunamadı!</h3>
				    </center>
				    <?php
				}
				?>
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
	
</body>
</html>