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
$clearence = 0;

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
    if($stmt = $pdo->prepare("SELECT * FROM users WHERE uid = :uid")) {
        
        // PDO parametrelerini ayarla
        $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $bilgi = $stmt->fetch();
            if(isset($bilgi['permlevel']) && $bilgi['permlevel'] >= 1) {
                
                $clearence = 1;
            }
        }
    }
}else{
    
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $act = $_POST['action'];
    if($act == 'activityMailOpt') {
        
        $activityMailOpt = 0;
        if(isset($_POST['activityMailOpt'])) {
            
            $activityMailOpt = 1;
        }
        
        if(($activityMailOpt == 1 || $activityMailOpt == 0) && $stmt = $pdo->prepare("UPDATE users SET activityMailOpt = :newOpt WHERE uid = :uid")) {
            
            // PDO parametrelerini ayarla
            $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
            $stmt->bindParam(":newOpt", $activityMailOpt, PDO::PARAM_STR);
            $stmt->execute();
        }
        
        unset($stmt);
        unset($pdo);
        header('Location: settings.php?success');
        exit;
    }else if($act == 'passwordReset') {
        
        $error = '';
        $oldPass = $_POST['oldPass'];
        $newPass = $_POST['newPass'];
        $newPassc = $_POST['newPassConfirm'];
        
        if(empty($oldPass) || empty($newPass) || empty($newPassc)) {
            
            $error .= 'Gerekli alanlardan bazılarını boş bıraktınız!' . PHP_EOL;
        }
        
        if($newPass != $newPassc) {
            
            $error .= 'Yeni şifre ve şifre doğrulama kısmına farklı şifreler girdiniz!' . PHP_EOL;
        }
        
        if($newPass == $bilgi['user']) {
            
            $error .= 'Yeni şifre kullanıcı adınız ile aynı olamaz!' . PHP_EOL;
        }
        
        $pass = chkUnsafePass($newPass);
        if($pass != 1) {
            
            $error .= $pass;
        }
        
        if(!password_verify($oldPass, $bilgi['pass'])) {
            
            $error .= 'Yazdığınız eski şifre şu anki şifreniz ile uyuşmuyor!' . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("UPDATE users SET pass = :pass WHERE uid = :id")) {
                
                $newPassHashed = password_hash($newPass, PASSWORD_DEFAULT);
                
                $stmt->bindParam(":pass", $newPassHashed, PDO::PARAM_STR);
                $stmt->bindParam(":id", $_SESSION['uid'], PDO::PARAM_STR);
                
                $stmt->execute();
            }
            
            unset($newPass);
            unset($newPassc);
            unset($oldPass);
            
            unset($stmt);
            unset($pdo);
            
            header('Location: logout.php');
            exit;
        }
        
    }else if($act == 'deleteAccount') {
        
        if($bilgi['permlevel'] > 1) {
        
            if($stmt = $pdo->prepare("DELETE from adminInfo WHERE id = :uid")) {
                
                $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
                $stmt->execute();
            }
        }
        
        if($stmt = $pdo->prepare("DELETE from etkinlikKatilim WHERE uid = :uid")) {
            
            $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
            $stmt->execute();
        }
        
        if($stmt = $pdo->prepare("DELETE from users WHERE uid = :uid")) {
            
            $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
            $stmt->execute();
        }
        
        unset($stmt);
        unset($pdo);
        header('Location: logout.php');
        exit;
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Hesap ayarları</title>
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
	
	<?php
	
	if($clearence == 1) {
	
	?>
	<!-- Additional header section  -->
	<nav class="nav-section">
		<div class="container">
			<!--<div class="nav-right">
			    <li><a href=""> Ek yönetici paneli</a></li>
			</div>-->
			<ul class="main-menu">
				<?=/* YK ek menü navigasyon barını göster */ file_get_contents('tmpller/ykUyeAltEkMenu.tmpl');?>
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
			<span>Ayarlar</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>YUIN Club hesap ayarların</h3>
					<p>Aşağıda sizinle ilgili sakladığımız tüm kişisel bilgilerinizi görüntüleyebilirsiniz. <b>Aşağıdaki bilgilerin güncellenmesi için lütfen yönetim kadrosundaki başkan, başkan yardımcısı ya da genel koordinatör'e danışınız.</b></p>
				</div>
				
				<form method="post" class="comment-form --contact">
				    <center>
				    <input type="hidden" name="action" value="activityMailOpt">
				    
				    <div class="col-lg-6">
				        <label for="user"><p>Kayıtlı öğrenci numaranız</p></label>
					    <input type="text" id="user" value="<?=$bilgi['user'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="name"><p>İsminiz</p></label>
					    <input type="text" id="name" value="<?=$bilgi['name'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="surname"><p>Soyisminiz</p></label>
					    <input type="text" id="surname" value="<?=$bilgi['surname'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="faculty"><p>Fakülteniz</p></label>
					    <input type="text" id="faculty" value="<?=$bilgi['faculty'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="department"><p>Bölümünüz</p></label>
					    <input type="text" id="department" value="<?=$bilgi['department'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="email"><p>Kayıtlı eposta adresiniz</p></label>
					    <input type="text" id="email" value="<?=$bilgi['email'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="phonenum"><p>Kayıtlı telefon numaranız</p></label>
					    <input type="text" id="phonenum" value="<?=$bilgi['phonenum'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="created_at"><p>YUIN Club'a kaydolma tarihiniz</p></label>
					    <input type="text" id="created_at" value="<?=$bilgi['created_at'];?>" readonly>
				    </div>
				    
				    <label for="activityMailOpt"> Bilişim Kulübü'nün düzenlediği etkinlikler ile ilgili eposta almak istiyorum</label>
				    <?php
				    
				    if($bilgi['activityMailOpt'] == 0) {
				        
				    ?>
				    <input type="checkbox" id="activityMailOpt" name="activityMailOpt" value="1"><br><br>
				    <?php
				    
				    }else{
				        
				    ?>
				    <input type="checkbox" id="activityMailOpt" name="activityMailOpt" value="0" checked><br><br>
				    <?php
				    
				    }
				    
				    ?>
				    <button class="site-btn">Gönder</button>
				    </center>
				</form>
				<hr>
				<form method="post" class="comment-form --contact">
				    <input type="hidden" name="action" value="passwordReset">
				        <div class="contact-form spad pb-0">
				        <div class="section-title text-center">
					        <h3>Hesap şifresi değişikliği</h3>
					        <p>YUIN Club hesabınızın şifresini buradan hızlıca değiştirebilirsiniz.</p>
					        <h6 style="color:red;"><?php if(isset($error)): echo $error; endif;?></h6>
				        </div>
						<center>
						<div class="col-lg-6">
							<input type="password" name="oldPass" placeholder="Eski şifrenizi girin" required>
						</div>
						<div class="col-lg-6">
							<input type="password" name="newPass" placeholder="Yeni şifrenizi girin" required>
						</div>
						<div class="col-lg-6">
							<input type="password" name="newPassConfirm" placeholder="Yeni şifrenizi doğrulayın" required>
						</div>
						<div class="col-lg-6">
							
								<button class="site-btn">Gönder</button>
							
						</div>
						</center>
					</div>
				</form>
				<hr>
				<script>
				    
				    function deleteAccount(form) {
				        
				        if(confirm("ONAY 1\nBu işlem YUIN Club hesabınızı, bizde kayıtlı tüm kişisel bilgilerinizi ve kayıtlı olduğunuz etkinlikleri silecek.\nBunu yapmak istediğinizden emin misiniz?")) {
				            if(confirm("ONAY 2\nBu işlem YUIN Club hesabınızı, bizde kayıtlı tüm kişisel bilgilerinizi ve kayıtlı olduğunuz etkinlikleri silecek.\nBunu yapmak istediğinizden emin misiniz?")) {
				                
				                this.form.submit();
				            }
				        }
				    }
				</script>
				<form method="post" class="comment-form --contact" onsubmit="deleteAccount(this)">
				    <input type="hidden" name="action" value="deleteAccount">
				    <div class="contact-form spad pb-0">
				        <div class="section-title text-center">
					        <h3>YUIN Club'dan ayrıl</h3>
					        <p>Umarız ki burayı kimse kullanmaz :) YUIN Club'dan ayrılmak için alttaki butona basabilirsiniz. Bu buton, bizde kayıtlı olan ve yukarıda gösterilen tüm kişisel verileriniz ile birlikte YUIN Club hesabınızı <b><i>KALICI</i></b> olarak silecektir.</p>
					        <br>
					        <button class="site-btn" style="background: #b00c00;"><i class="far fa-trash-alt"></i> YUIN Club'dan ayrıl</button>
				        </div>
				    </div>
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


	<!-- load for map -->
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0YyDTa0qqOjIerob2VTIwo_XVMhrruxo"></script>
	<script src="js/map.js"></script>
	
</body>
</html>