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

// Kategorileri getir
$katList = [];
if($stmt = $pdo->prepare("SELECT DISTINCT(alt) FROM galeri")) {
    
    if($stmt->execute()) {
        
        $katListGet = $stmt->fetchAll();
    }
}

// Ne ben sorayım ne sen söyle... Çalışıyorsa eleştiri kabul etmiyorum çünkü ben bir yazılımcıyım.
foreach($katListGet as $katt) {
    
    $katList[] = $katt['alt'];
}

$kat = false;
if(isset($_GET['kategori']) && !empty(trim($_GET['kategori']))) {
    
    $kat = trim($_GET['kategori']);
    if(!in_array($kat, $katList)) {
        
        $kat = false;
    }
}

if($kat) {
    // Lütfen çökme...
    if($stmt = $pdo->prepare("SELECT * FROM galeri WHERE alt = :alt")) {
        $stmt->bindParam(':alt', $kat, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $photos = $stmt->fetchAll();
        }else{
            
            echo '<h3>Bir hata gerçekleşti. Lütfen sayfayı yenileyin veya daha sonra tekrar deneyin.</h3>';
            exit;
        }
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Galeri</title>
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
			<span>Galeri</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
				<div class="section-title text-center">
					<h3>Galeri</h3>
					<?php
					if($kat) {
					    
					    ?>
					    <h4>Kategori gösteriliyor. Geri gönmek için <a href="gallery.php">buraya tıklayın.</a></h4>
					    <?php
					}else{
					?>
					<h4>İlgili fotoğrafları görmek için lütfen aşağıdan kategori seçiniz.</a></h4>
					<?php
					}
					?>
				</div>
				<div class="row">
				<?php
				
				if($kat) {
				    foreach($photos as $photo) {
				        
				        // Hızlı fix... yerel dizin ile web dizini doğal olarak farklı...
				        $photo['img'] = str_replace(YUIN_GALLERY_DIRECTORY, 'https://yuin.yeditepe.edu.tr/img/galeri/', $photo['img']);
				    ?>
				    
				    <div class="col-lg-4 col-md-6 course-item">
					    <div class="course-thumb">
					        <a href="<?=$photo['img'];?>" target="_blank">
					    	    <img src="<?=$photo['img'];?>">
					    	</a>
					    </div>
					    <div class="course-info">
					    	<h6><?=$photo['alt'];?></h6><br>
					    </div>
				    </div>
				    
				    <?php
				    }
				}else{
				    
				    foreach($katList as $kattt) {
				    ?>
				    
				    <div class="col-lg-4 col-md-6 course-item">
					    <a href="?kategori=<?=$kattt;?>">
					    <div class="course-thumb">
					    	<img src="https://yuin.yeditepe.edu.tr/img/image-gallery.png" alt="Fotoğraf kategorisi <?=$kattt;?>">
					    </div>
					    <div class="course-info">
					    	<h6><?=$kattt;?></h6><br>
					    </div>
					    </a>
				    </div>
				    
				    <?php
				    }
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