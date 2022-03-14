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

if(!isset($_GET['getSubgPageId'])) {
    
    if($stmt = $pdo->prepare("SELECT * FROM subgroups")) {
        
        if($stmt->execute()) {
            
            $subgroups = $stmt->fetchAll();
        }
    }
}else{
    
    if(is_numeric($_GET['getSubgPageId'])) {
        
        $pid = $_GET['getSubgPageId'];
        
        if($stmt = $pdo->prepare("SELECT id,banner,subgroups.name,topic,html,users.name AS username,users.surname AS usersurname,phonenum,email FROM subgroups INNER JOIN users ON subgroups.admin=users.uid WHERE id = :id")) {
            
            $stmt->bindParam(":id", $pid, PDO::PARAM_STR);
            if($stmt->execute()) {
                
                $subgroups = $stmt->fetch();
            }
        }
        
        $htmlOut = str_replace("%bannerSrc%", $subgroups['banner'], $subgroups['html']);
        $htmlOut = str_replace("%name%", $subgroups['name'], $htmlOut);
        $htmlOut = str_replace("%topic%", $subgroups['topic'], $htmlOut);
        $htmlOut = str_replace("%email%", $subgroups['email'], $htmlOut);
        $htmlOut = str_replace("%phone%", $subgroups['phonenum'], $htmlOut);
        $htmlOut = str_replace("%username%", $subgroups['username'], $htmlOut);
        $htmlOut = str_replace("%usersurname%", $subgroups['usersurname'], $htmlOut);
        
    }else{
        
        unset($stmt);
        unset($pdo);
        header('Location: subgroup.php');
        exit;
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Alt Gruplarımız</title>
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
			<span>Alt Gruplar</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>Alt gruplarımız</h3>
					<p><b>Bilişim</b> çok geniş bir konu. Bu geniş konu içerisinde herkesin ilgi duyduğu alanlar farklı. Bizde tüm alanlara hakim olabilmek için alt gruplar oluşturduk. Altta tüm grupları görebilirsiniz.</p>
				</div>
				
			    <?php
			    
			    if(!isset($_GET['getSubgPageId'])) {
			        
			        echo '<div class="row">';
			    }
			    
			    if(!isset($_GET['getSubgPageId'])) {
			    
			        foreach($subgroups as $subgroup) {
			        
			        ?>
			        
			        <div class="col-lg-4 col-md-6 course-item">
					    <div class="course-thumb">
					    	<img src="<?=$subgroup['banner'];?>">
					    </div>
					    <div class="course-info">
					    	<h4><?=$subgroup['name'];?></h4><br>
					    	<h6><?=$subgroup['topic'];?></h6>
					    </div>
					    <center>
					        <br>
					        <a href="subgroup.php?getSubgPageId=<?=$subgroup['id'];?>"><button class="site-btn">Grup sayfasını gör</button></a>
					    </center>
				    </div>
			        
			        <?php
			        }
			    }else if(!empty($_GET['getSubgPageId']) && is_numeric($_GET['getSubgPageId'])){
			        
			        ?>
			        
			        <center>
			            <h3><?=$subgroups['name'];?></h3>
			            <p><?=$subgroups['topic'];?></p>
			        </center>
			        
			        <?=$htmlOut;?>
			        
			        </div>
			        <?php
			    }
			    
			    if(!isset($_GET['getSubgPageId'])) {
			        
			        echo '</div>';
			    }
			    
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
	
</body>
</html>