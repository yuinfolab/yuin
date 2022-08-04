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

$views = false;
if($stmt = $pdo->prepare("SELECT varval FROM stats WHERE cid = :cid")) {
    
    // PDO parametrelerini ayarla
    $cidv = 1;
    $stmt->bindParam(":cid", $cidv, PDO::PARAM_STR);
    if($stmt->execute()) {
        
        $views = $stmt->fetch();
        if(is_array($views)) {
            
            $views = (int)$views['varval'];
        }
    }
    
    if(is_numeric($views) && !isset($_GET['yorumlariGoster'])) {
        
        $views++;
        
        if($stmt = $pdo->prepare("UPDATE stats SET varval = :nvarval WHERE cid = :cid")) {
            
            // PDO parametrelerini ayarla
            $stmt->bindParam(":cid", $cidv, PDO::PARAM_STR);
            $stmt->bindParam(":nvarval", $views, PDO::PARAM_STR);
            $stmt->execute();
        }
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | E-Sergi</title>
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
			<span>YUIN E-Sergi</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>E-Sergi</h3>
					
				</div>
				<center>
			    <iframe style="width: 85%; height: 720px" src="https://www.artsteps.com/embed/5fba826683d339612bf4c689/560/315" frameborder="0" allowfullscreen></iframe>
			    <p><i class="fas fa-eye"></i> <i>E-Sergi <?=$views;?> kere görüntülendi.</i></p>
			    <?php
			    
			    if(isset($_GET['yorumlariGoster'])) {
			        
			        if($login) {
			            
			            $shoutboxIsim = $bilgi['name'] . ' ' . $bilgi['surname'];
			        }else{
			            
			            $shoutboxIsim = 'Ziyaretçi';
			        }
			        
			        ?>
			        
			        <?php
			        
			    }else{
			    
			    ?>
			        <!--<a href="?yorumlariGoster"><button class="site-btn"><i class="fas fa-comments"></i> Yorumlar</button></a>-->
			    <?php
			    
			    }
			    
			    ?>
			    <p>Paylaşımcı olmak için <b>yuinformaticsergi@gmail.com, yuinformatics@gmail.com veya yuin@yeditepe.edu.tr</b> eposta adreslerinden bizimle iletişime geçebilirsiniz<br><b>VEYA</b><br>Alttaki butona tıklayarak hazır şablon ile sergi eser paylaşımı bildiriminde bulunabilirsiniz</p>
			    <a href="mailto:yuinformaticsergi@gmail.com?subject=SERGİ ESER PAYLAŞIMI&body=Eserin beyaz bir arkaplanda görünür bir şekilde fotoğraflanması
%0D%0A %0D%0A
Paylaşımcı Ad-Soyad: (Örn: Baransel Çelik)
%0D%0A
Eser Markası ve Modeli: (Örn: Nokia 6310i)
%0D%0A
*Eser Hakkında Bilgi: (Örn: Nokia 6310i, GPRS üzerinden internete bağlanılabildiğini gösteren ilk telefondur. 47 milimetre genişliğine ve 129 milimetre uzunluğuna sahip olan bu telefon, çıkarılabilir. Li-Po 600 mAh bataryası ile 17 gün boyunca açık kalabilmektedir.)
%0D%0A
**Eserin Piyasaya Sürüldüğü Tarih: (Örn: 2001) 
%0D%0A %0D%0A
*Eğer eser hakkında bir bilgi yok ise bu alan boş bırakılabilir.
%0D%0A
**Tam tarih bilinmiyorsa şu şekilde de yazılabilir; 2000’li yıllar."><button class="site-btn"><i class="fas fa-hand-holding-medical"></i> Paylaşımcı olmak istiyorum</button></a>
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
	
</body>
</html>