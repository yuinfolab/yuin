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
    if($stmt = $pdo->prepare("SELECT user,name,surname,permlevel FROM users WHERE uid = :uid")) {
        
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

if($bilgi['permlevel'] == 1) {
    
    unset($stmt);
    unset($pdo);
    echo '<script>alert("Üzgünüm!\n\nBu sayfaya ya da içeriğe erişmek için yeterli yetki seviyesine sahip değilsin. Erişim engellendi!");window.location.replace("http://yuin.yeditepe.edu.tr/index.php");</script>';
    exit;
}

if($bilgi['permlevel'] < 2) {
    
    unset($stmt);
    unset($pdo);
    header('Location: logout.php');
    exit;
}

$specificAdmin = 0;
if(isset($_GET['adminsAdministration']) && !empty($_GET['adminsAdministration']) && is_numeric($_GET['adminsAdministration'])) {
    
    $specificAdmin = 1;
    $adminToAdmin = $_GET['adminsAdministration'];
    if($stmt = $pdo->prepare("SELECT * FROM users INNER JOIN adminInfo ON users.uid=adminInfo.id WHERE uid = :id")) {
        
        $stmt->bindParam(":id", $adminToAdmin, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $adms = $stmt->fetch();
        }
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'editAdm') {
        
        $error = '';
        $updatePhoto = false;
        
        $photo = @$_FILES['photo']['tmp_name'];
        if(isset($photo) && !empty($photo)) {
            
            $updatePhoto = true;
        }
        
        if($updatePhoto == true) {
        
            $photoExt = $_FILES['photo']['type'];
            
            if($_FILES['photo']['size'] < 0 || !getimagesize($_FILES['photo']['tmp_name'])) {
                
                unlink($_FILES['photo']['tmp_name']);
                $error .= "Lütfen fotoğraf yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            if(empty($photo)) {
                
                $error .= "Fotoğraf boş olamaz!" . PHP_EOL;
            }else{
                
                $photo = base64_encode($photo);
                unlink($_FILES['photo']['tmp_name']);
                $photo = 'data:' . $photoExt . ';base64,' . $photo;
            }
        }
        
        $mission = trim($_POST['mission']);
        $priority = trim($_POST['priority']);
        
        if(empty($mission)) {
            
            $error .= "Görev boş olamaz!" . PHP_EOL;
        }
        
        if(empty($priority)) {
            
            $error .= "Öncelik boş olamaz!" . PHP_EOL;
        }
        
        if(empty($error)) {
            
            $sqlqry = "UPDATE adminInfo SET mission = :mission, priority = :priority WHERE id = :id";
            if($updatePhoto == true) {
                
                $sqlqry = "UPDATE adminInfo SET photo = :photo, mission = :mission, priority = :priority WHERE id = :id";
            }
            
            if($stmt = $pdo->prepare($sqlqry)) {
                
                if($updatePhoto == true) {
                    
                    $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                }
                
                $stmt->bindParam(":mission", $mission, PDO::PARAM_STR);
                $stmt->bindParam(":priority", $priority, PDO::PARAM_STR);
                $stmt->bindParam(":id", $adminToAdmin, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    
                    header('Location: adminsAdministration.php');
                    exit;
                }
            }
        }
    }
    
}else{
    
    if($stmt = $pdo->prepare("SELECT * FROM users INNER JOIN adminInfo ON users.uid=adminInfo.id WHERE permlevel = 2")) {
            
        if($stmt->execute()) {
            
            $adms = $stmt->fetchAll();
        }
    }
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | YK üyelerini yönet</title>
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
			<a href="#"><i class="fa fa-home"></i> Ana Sayfa</a> <i class="fa fa-angle-right"></i> YK Panel <i class="fa fa-angle-right"></i>
			<span>YK üyelerini yönet</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>YK üyelerini yönet</h3>
					
				</div>
				
				<?php
				
				if($specificAdmin == 0) {
				
				?>
				<table>
				    <tr>
				        <td>ID</td>
				        <td>Fotoğraf</td>
				        <td>Görev</td>
				        <td>Öğrenci No.</td>
				        <td>Öncelik</td>
				        <td>İsim</td>
				        <td>Soyisim</td>
				        <td>Katılım tarihi</td>
				        <td>Yönet</td>
				    </tr>
				    <?php
				        
				        foreach($adms as $adm) {
				    ?>
				    <tr>
				        <td><?=$adm['id'];?></td>
				        <td><img src="<?=$adm['photo'];?>" style="width:150px;height:150px;" alt="Foto"></td>
				        <td><?=$adm['mission'];?></td>
				        <td><?=$adm['user'];?></td>
				        <td><?=$adm['priority'];?></td>
				        <td><?=$adm['name'];?></td>
				        <td><?=$adm['surname'];?></td>
				        <td><?=$adm['created_at'];?></td>
				        <td><a href="adminsAdministration.php?adminsAdministration=<?=$adm['id'];?>"><button class="site-btn">YÖNET</button></a></td>
				    </tr>
				    <?php
				        }
				    ?>
				</table>
				<?php
				}else{
				?>
				<center>
				<h3>Spesifik bir YK üyesini yönet</h3>
				<p>YK üye numarası: <?=$adms['id'];?></p>
				<hr>
				<form class="comment-form --contact" method="post" enctype="multipart/form-data">
				    <input type="hidden" name="action" value="editAdm">
				    <div class="col-lg-6">
				        <label for="user">Öğrenci numarası</label>
					    <input type="text" id="user" name="user" value="<?=$adms['user'];?>" readonly>
				    </div>
				    <div class="col-lg-6">
				        <label for="photo">Profil fotoğrafı<br><b>NOT: Yüksek çözünürlüklü fotoğraflar doğru yüklenmeyebilir.</b></label>
					    <input type="file" id="photo" name="photo" value="">
				    </div>
				    <div class="col-lg-6">
				        <label for="mission">Görevi</label>
					    <input type="text" id="mission" name="mission" placeholder="ÖRN: Dış İlişkiler" value="<?=$adms['mission'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="priority">Öncelik</label>
					    <input type="text" id="priority" name="priority" placeholder="Daha küçük olan daha önce gösterilir." value="<?=$adms['priority'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <button class="site-btn">YK üyesini güncelle</button>
				    </div>
				</form>
				</center>
				<?php
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