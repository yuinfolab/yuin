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
}else{
    
    unset($stmt);
    unset($pdo);
    header('Location: login.php?loginToProceed');
    exit;
}

// Etkinlikleri getir

$etkListe = '';
$error = '';

if($stmt = $pdo->prepare("SELECT id,banner,tag,info,location,date FROM etkinlikKatilim INNER JOIN etkinlik ON etkinlikKatilim.eid=etkinlik.id WHERE uid = :uid")) {
    
    $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
    if($stmt->execute()) {
        
        $etkListe = $stmt->fetchAll();
    }
}

/*

10.05.2020 Optimizasyon. Bu kısım komple kaldırılıp üstteki sorguya inner join eklendi.

if(!empty($etkListe)) {

    if(is_array($etkListe)) {
        
        $error .= "Üzgünüm (1) beklenmedik bir hata meydana geldi!" . PHP_EOL;
    }
    
    $sqlqry = 'SELECT * FROM etkinlik WHERE';
    foreach($etkListe[0] as $etkId) {
        
        $sqlqry .= ' id = ' . $etkId . ' AND';
    }
    $sqlqry = substr($sqlqry, 0, strrpos($sqlqry, ' '));
    
    if($stmt = $pdo->prepare($sqlqry)) {
        
        if($stmt->execute()) {
            
            $etkListe = $stmt->fetchAll();
        }
    }
}else{
    
    
}*/

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | İletişim</title>
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
			<a href="#"><i class="fa fa-home"></i> Aa Sayfa</a> <i class="fa fa-angle-right"></i>
			<span>Brifing</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			<div class="contact-form spad pb-0">
				<div class="section-title text-center">
					<h3>Etkinliklerim</h3>
					<p>Katıldığınız tüm etkinliklerin yer/zaman bilgilerine buradan ulaşabilirsiniz.</p>
					<br>
					<p><b>Bugüne kadar katıldığınız toplam etkinlik sayısı: <?=@count($etkListe);?></b></p>
				</div>
				
				<div class="row">
				<?php
				
				if(!empty($etkListe)) {
				    
				    foreach($etkListe as $etk) {
				        
				?>
				<div class="col-lg-4 col-md-6 course-item">
					<div class="course-thumb">
						<img src="<?=$etk['banner'];?>">
						<div class="course-cat">
							<span><?=$etk['tag'];?></span>
						</div>
						
					</div>
					<div class="course-info">
						<h4><?=$etk['info'];?></h4><br>
						<div class="date"><i class="fa fa-clock-o"></i><?=$etk['location'];?></div>
						<div class="date"><i class="fas fa-map-marker-alt"></i><?="\t" . date('d/m/Y d:i', $etk['date']);?></div>
						<h5 style="color:green;"><i class="fas fa-check"></i> Bu etkinliğe katıldınız</h5>
					</div>
				</div>
				<?php
				    }
				}else{
				    
				    ?>
				    <div style="color:red;" class="text-center">
				    <h3 style="color:red;" class="text-center"><b>Henüz hiçbir etkinliğe katılmadınız.</b></h3>
				    </div>
				    <?php
				}
				?>
				</div>
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