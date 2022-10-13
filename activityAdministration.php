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

// Şifreleri getir
require_once "/home/yuinyeditepe/public_html/protected/protected_constants.php";

// Göstermelik
$yuinEmailSender = YUIN_SMTP_ACCT;
$yuinEmailPass = YUIN_SMTP_PASS;
$yuinEmailSMTP = YUIN_SMTP;

$yuinEmailPassLen = strlen($yuinEmailPass) - 4;
$yuinEmailPassMasked = $yuinEmailPass[0] . $yuinEmailPass[1] . $yuinEmailPass[2] . $yuinEmailPass[3] . str_repeat('*', $yuinEmailPassLen);

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

$specificAct = 0;

if(isset($_GET['administrateAct']) && !empty($_GET['administrateAct']) && is_numeric($_GET['administrateAct'])) {
    
    $actToAdmin = $_GET['administrateAct'];
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delAct') {
        
        if($stmt = $pdo->prepare("DELETE from etkinlikKatilim WHERE eid = :id")) {
            
            $stmt->bindParam(":id", $actToAdmin, PDO::PARAM_STR);
            if(!$stmt->execute()) {
                
                exit;
            }
        }
        
        if($stmt = $pdo->prepare("DELETE from etkinlik WHERE id = :id")) {
            
            $stmt->bindParam(":id", $actToAdmin, PDO::PARAM_STR);
            if($stmt->execute()) {
                
                unset($stmt);
                unset($pdo);
                header('Location: activityAdministration.php');
                exit;
            }else{
                
                exit;
            }
        }
    }
    
    $specificAct = 1;
    $afisLink = [];
    if($stmt = $pdo->prepare("SELECT id,tag,info,location,slots,date FROM etkinlik WHERE id = :id")) {
        
        $stmt->bindParam(":id", $actToAdmin, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $acts = $stmt->fetch();
            
            foreach($acts as $k => $v) {
                
                $thisId = $acts['id'];
                $afisLink['banner-' . $thisId] = 'backend/asyncB64Loader.php?eid=' . $thisId;
            }
        }else{
            
            exit;
        }
    }
    $afisLink = json_encode($afisLink);
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'editAct') {
        
        $error = '';
        $updateBanner = false;
        $banner = @$_FILES['banner']['tmp_name'];
        if(isset($banner) && !empty($banner)) {
            
            $updateBanner = true;
        }
        
        if($updateBanner == true) {
            
            $bannerExt = $_FILES['banner']['type'];
            
            if($_FILES['banner']['size'] < 0 || !getimagesize($_FILES['banner']['tmp_name'])) {
                
                unlink($_FILES['banner']['tmp_name']);
                $error .= "Lütfen fotoğraf yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $banner = file_get_contents($_FILES['banner']['tmp_name']);
            
            /*if(!empty($banner)) {
                
                //$error .= "Afiş boş olamaz!" . PHP_EOL;
                $updateBanner = true;
            }*/
            
            $banner = base64_encode($banner);
            unlink($_FILES['banner']['tmp_name']);
            $banner = 'data:' . $bannerExt . ';base64,' . $banner;
        }
        
        $tag = $_POST['tag'];
        $info = $_POST['info'];
        $location = $_POST['location'];
        $date = $_POST['date'];
        $time = $_POST['datetime'];
        $slots = $_POST['slots'];
        
        if(empty($tag)) {
            
            $error .= "Etiket boş olamaz!" . PHP_EOL;
        }
        
        if(empty($info)) {
            
            $error .= "Etkinlik başlığı boş olamaz!" . PHP_EOL;
        }
        
        if(empty($location)) {
            
            $error .= "Konum boş olamaz!" . PHP_EOL;
        }
        
        if(empty($date) || empty($time)) {
            
            $error .= "Etkinlik tarihi boş olamaz ve sayısal bir değer olmak zorundadır!" . PHP_EOL;
        }
        
        $date = strtotime($date . ' ' . $time);
        if(empty($date)) {
            
            $error .= "Etkinlik tarihi Epoch zaman sistemine dönüştürülürken bir hata meydana geldi!" . PHP_EOL;
        }
        
        if(empty($slots) || !is_numeric($slots)) {
            
            $error .= "Kontenjan boş olamaz ve sayısal bir değer olmak zorundadır!" . PHP_EOL;
        }
        
        if(empty($error)) {
            
            $sqlqry = "UPDATE etkinlik SET tag = :tag, info = :info, location = :location, date = :date, slots = :slots WHERE id = :id";
            
            if($updateBanner == true) {
                
                $sqlqry = "UPDATE etkinlik SET banner = :banner, tag = :tag, info = :info, location = :location, date = :date, slots = :slots WHERE id = :id";
            }
            
            if($stmt = $pdo->prepare($sqlqry)) {
                
                if($updateBanner == true) {
                    
                    $stmt->bindParam(":banner", $banner, PDO::PARAM_STR);
                }
                
                $stmt->bindParam(":tag", $tag, PDO::PARAM_STR);
                $stmt->bindParam(":info", $info, PDO::PARAM_STR);
                $stmt->bindParam(":location", $location, PDO::PARAM_STR);
                $stmt->bindParam(":date", $date, PDO::PARAM_STR);
                $stmt->bindParam(":slots", $slots, PDO::PARAM_STR);
                $stmt->bindParam(":id", $actToAdmin, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    
                    header('Location: activityAdministration.php');
                    exit;
                }else{
                    
                    exit;
                }
            }
        }
        
    }
    
}else if(isset($_GET['viewActAttendance']) && !empty($_GET['viewActAttendance']) && is_numeric($_GET['viewActAttendance'])) {
    
    $actToView = $_GET['viewActAttendance'];
    if($stmt = $pdo->prepare("SELECT * FROM etkinlik INNER JOIN etkinlikKatilim ON etkinlik.id=etkinlikKatilim.eid INNER JOIN users ON users.uid=etkinlikKatilim.uid WHERE id = :id")) {
        
        $stmt->bindParam(":id", $actToView, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $acttt = $stmt->fetchAll();
        }else{
            
            exit;
        }
    }
    
}else{
    
    if($stmt = $pdo->prepare("SELECT id, tag, info, location, date, slots FROM etkinlik GROUP BY date DESC")) {
        
        if($stmt->execute()) {
            
            $acts = $stmt->fetchAll();
        }else{
            
            exit;
        }
    }
    
    if($stmt = $pdo->prepare("SELECT * FROM etkinlikKatilim")) {
        
        if($stmt->execute()) {
            
            $actsJoined = $stmt->fetchAll();
        }else{
            
            exit;
        }
    }
    
    $attd = [];
    $time = time();
    
    foreach($acts as $act) {
        
        if($act['date'] > $time) {
            
            $katilimciCounter = 0;
            foreach($actsJoined as $actsJoins) {
                
                if($actsJoins['eid'] == $act['id']) {
                    
                    $katilimciCounter++;
                }
            }
            
            $attd[$act['id']] = $katilimciCounter;
        }else{
            
            $attd[$act['id']] = '-';
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'addAct') {
    
    $error = '';
        $banner = $_FILES['banner']['tmp_name'];
        $bannerExt = $_FILES['banner']['type'];
        
        if($_FILES['banner']['size'] < 0 || !getimagesize($_FILES['banner']['tmp_name'])) {
            
            unlink($_FILES['banner']['tmp_name']);
            $error .= "Lütfen fotoğraf yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
        }
        
        $banner = file_get_contents($_FILES['banner']['tmp_name']);
        
        $tag = $_POST['tag'];
        $info = $_POST['info'];
        $location = $_POST['location'];
        $date = $_POST['date'];
        $time = $_POST['datetime'];
        $slots = $_POST['slots'];
        
        if(empty($banner)) {
            
            $error .= "Afiş boş olamaz!" . PHP_EOL;
        }
        
        $banner = base64_encode($banner);
        unlink($_FILES['banner']['tmp_name']);
        $banner = 'data:' . $bannerExt . ';base64,' . $banner;
        
        if(empty($tag)) {
            
            $error .= "Etiket boş olamaz!" . PHP_EOL;
        }
        
        if(empty($info)) {
            
            $error .= "Etkinlik başlığı boş olamaz!" . PHP_EOL;
        }
        
        if(empty($location)) {
            
            $error .= "Konum boş olamaz!" . PHP_EOL;
        }
        
        if(empty($date) || empty($time)) {
            
            $error .= "Etkinlik tarihi boş olamaz ve sayısal bir değer olmak zorundadır!" . PHP_EOL;
        }
        
        $date = strtotime($date . ' ' . $time);
        if(empty($date)) {
            
            $error .= "Etkinlik tarihi Epoch zaman sistemine dönüştürülürken bir hata meydana geldi!" . PHP_EOL;
        }
        
        if(empty($slots) || !is_numeric($slots)) {
            
            $error .= "Kontenjan boş olamaz ve sayısal bir değer olmak zorundadır!" . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("INSERT into etkinlik (banner, tag, info, location, date, slots) VALUES (:banner, :tag, :info, :location, :date, :slots)")) {
                
                $stmt->bindParam(":banner", $banner, PDO::PARAM_STR);
                $stmt->bindParam(":tag", $tag, PDO::PARAM_STR);
                $stmt->bindParam(":info", $info, PDO::PARAM_STR);
                $stmt->bindParam(":location", $location, PDO::PARAM_STR);
                $stmt->bindParam(":date", $date, PDO::PARAM_STR);
                $stmt->bindParam(":slots", $slots, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    
                    header('Location: activityAdministration.php');
                    exit;
                }else{
                    
                    exit;
                }
            }
        }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'sendMail') {
    
    $error = '';
    
    $email = $_POST['email'];
    $title = $_POST['title'];
    $contentt = $_POST['contenttt'];
    
    $email = trim($email);
    $title = trim($title);
    $contentt = trim($contentt);
    
    if(empty($email)) {
        
        $error .= " Eposta hesabı boş olamaz! " . PHP_EOL;
    }
    
    if(empty($title)) {
        
        $error .= " Eposta konusu boş olamaz! " . PHP_EOL;
    }
    
    if(empty($contentt)) {
        
        $error .= " Eposta içeriği boş olamaz! " . PHP_EOL;
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $error .= " Eposta hesabı geçersiz! Yazdığınız eposta adreslerini virgülle ayırırken bir hata yapmış olabilirsiniz. " . PHP_EOL;
    }
    
    $contentt = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.=w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns=3D"http://www.w3.org/1999/xhtml" lang=3D"en"><head><meta charset="utf-8" lang="tr"><title>' . $title . '</title></head><body>' . $contentt . '</body></html>';
    
    if(empty($error)) {
        
        sleep(5); // 5 Saniye Bekle
        $s = sendEmail($email, $contentt, $title);
        if($s) {
            
            echo $email . ' Eposta basariyla gonderildi!';
        }else{
            
            echo $email . ' Eposta gonderilemedi!';
        }
    }else{
        
        echo $error;
        
    }
    
    unset($stmt);
    unset($pdo);
    exit; // Bu kısım bir API. HTML çıktısına ihtiyacımız yok çünkü jQuery AJAX ile browser'dan çağrılıyor.
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Etkinlikleri yönet</title>
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
			<span>Etkinlikleri yönet</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->
	
	<script>
	
	function sleepold(milliseconds) {
        
        const date = Date.now();
        let currentDate = null;
        do {
            
            currentDate = Date.now();
        } while (currentDate - date < milliseconds);
    }
    
    function sleep(ms) {
        
        return new Promise(resolve => setTimeout(resolve, ms));
    }
	
	function HttpPostMailYolla(i, total, recipientMail, mailTitle, mailIcerik) {
	    
	    $(document).ready(function(){
                $.post("activityAdministration.php",
                    {
                        action: "sendMail",
                        email: recipientMail,
                        title: mailTitle,
                        contenttt: mailIcerik,
                    },
                function(data, status){
                    
                    if(data.includes("basariyla")) {
                        
                        console.log("Eposta gonderimi " + recipientMail + " adresine basarili.");
	                    lastSent = document.getElementById("mailAlertBox").innerHTML;
                        lastSent = lastSent + "<b>(" + (i + 1) + "/" + total + ")</b> " + recipientMail + " gönderim başarılı<br>";
                        document.getElementById("mailAlertBox").innerHTML = lastSent;
                        
                        return true;
                    }else{
                        
                        alert("Eposta gönderimi başarısız oldu: " + recipientMail + "   " + status + "\n\nOlay logu: \n" + data);
                    }
                    
                    return false;
                });
                
            });
	}
	
	function sendEmailTekerTeker() {
	    
	    var recipients = document.getElementById("recipient").value;
	    var mailTitle = document.getElementById("title").value;
	    var mailIcerik = document.getElementById("contentt").value;
	    
	    recipients = recipients.toString();
	    recipients = recipients.split(',');
	    
	    if(recipients == undefined) {
	        
	        alert("Alıcı listesinde bir yanlışlık var! Epostaları virgülle doğru bir şekilde ayırdığınızdan eminmisiniz?\nLütfen kontrol edip tekrar deneyiniz.");
	        return;
	    }
	    
	    if(mailTitle == undefined) {
	        
	        alert("Eposta konusu kısmında bir eksiklikmi var? İçeriği çekemedim.\nLütfen kontrol edip tekrar deneyiniz.");
	        return;
	    }
	    
	    if(mailIcerik == undefined) {
	        
	        alert("Eposta içeriği kısmında bir eksiklikmi var? İçeriği çekemedim.\nLütfen kontrol edip tekrar deneyiniz.");
	        return;
	    }
	    
	    var lastSent;
	    
	    document.getElementById("epostaGonderiWarningTag").style.display="block";
	    console.log( JSON.stringify(recipients, null, 2) ); // Debug
	    
	    for(var i = 0; i < recipients.length; i++) {
	        
	        console.log("Eposta gonderimi " + recipients[i] + " adresine başlatıldı.");
	        HttpPostMailYolla(i, recipients.length, recipients[i], mailTitle, mailIcerik);
	        
	    }
	}
	
	</script>


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		
			
				<div class="section-title text-center">
					<h3>Etkinlikleri yönet</h3>
					
				</div>
				
				<?php
				
				if(isset($actToAdmin) && !empty($actToAdmin)) {
				?>
				<center>
				    <h3>Spesifik bir etkinliği yönet</h3>
				    <p>Etkinlik numarası <?=$actToAdmin;?></p>
			        <?php
			        
			        if(isset($error)) {
			        ?>
			        <p style="color:red;"><?=$error;?></p>
			        <?php
			        }
			        ?>
			    </center>
			    <hr>
			    <center>
			    <form method="post">
			        
			        <input type="hidden" name="action" value="delAct">
			        <button class="site-btn" style="background: #b00c00;"><i class="far fa-trash-alt"></i> Etkinliği sil</button>
			    </form>
			    </center>
			    <br>
			    <form class="comment-form --contact" method="post" enctype="multipart/form-data">
			        
			        <input type="hidden" name="action" value="editAct">
			        <center>
				    <div class="col-lg-6">
				        <label for="banner"><p>Etkinlik afişi<br><b>NOT: Yüksek çözünürlüklü fotoğraflar doğru yüklenmeyebilir. Yükledikten sonra fotoğrafı kontrol edin.</b></p></label><br>
				        <img id="banner-<?=$acts['id'];?>" src="img/loading.gif" alt="banner" style="width:540px;height:540px;"><br>
					    <input id="banner-<?=$acts['id'];?>" type="file" id="banner" name="banner" value="">
				    </div>
				    <div class="col-lg-6">
				        <label for="tag"><p>Etkinlik etiketi <b>(Afişin altındaki mavi kutucuk)</b></p></label>
					    <input type="text" id="tag" name="tag" value="<?=$acts['tag'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="info"><p>Etkinlik ismi</p></label>
					    <input type="text" id="info" name="info" value="<?=$acts['info'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="location"><p>Etkinlik konumu</p></label>
					    <input type="text" id="location" name="location" value="<?=$acts['location'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="date"><p>Etkinlik tarihi</p></label>
					    <input type="date" id="date" name="date" value="<?=date('d/m/Y', $acts['date']);?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="datetime"><p>Etkinlik saati</p></label>
					    <input type="time" id="datetime" name="datetime" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="slots"><p>Etkinlik kontenjanı</p></label>
					    <input type="text" id="slots" name="slots" value="<?=$acts['slots'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <button class="site-btn">Etkinlik bilgilerini güncelle</button>
				    </div>
			    </form>
			    <?php
				}else if(!isset($_GET['viewActAttendance'])) {
				?>
				
				<table>
				    <tr>
				        <td>ID</td>
				        <td>Afiş</td>
				        <td>Etiket</td>
				        <td>Başlık</td>
				        <td>Konum</td>
				        <td>Tarih</td>
				        <td>Kontenjan</td>
				        <td>Katılım</td>
				        <td>Yönet</td>
				    </tr>
				    <?php
				    
				    foreach($acts as $act) {
				    ?>
				    <tr>
				        <td><?=$act['id'];?></td>
				        <td><!--<img src="$act['banner'];" style="width:270px;height:270px;" alt="banner">--><b>Afişi görmek için YÖNET butonuna tıklayın.</b></td>
				        <td><?=$act['tag'];?></td>
				        <td><?=$act['info'];?></td>
				        <td><?=$act['location'];?></td>
				        <td><?=date('d-m-Y H:i', $act['date']);?></td>
				        <td><?=$act['slots'];?></td>
				        <td><?=$attd[$act['id']];?></td>
				        <td><a href="activityAdministration.php?administrateAct=<?=$act['id'];?>"><button class="site-btn">YÖNET</button></a><br><br><a href="activityAdministration.php?viewActAttendance=<?=$act['id'];?>"><button class="site-btn">KATILANLAR</button></a></td>
				    </tr>
				    <?php
				    }
				    ?>
				    </table>
				    <?php
				}else{
				    ?>
				    <script>
				        function showForms() {
				            
				            document.getElementById("epostaForm").style.display="block";
				            document.getElementById("invokejQryBtn").style.display="block";
				            document.getElementById("showFormsBtn").style.display="none";
				        }
				    </script>
				    <center><h6><?=$_GET['viewActAttendance'];?> numaralı etkinliğe katılan üyeler</h6>
				    <form id="epostaForm" class="comment-form --contact" method="post" enctype="multipart/form-data" style="display: none;">
				        <input type="hidden" name="action" value="sendMail">
				        <h5>Katılacak tüm üyelere toplu eposta gönder</h5><br>
				        <h6>DİKKAT! Gönderilecek eposta, <b><?=$yuinEmailSender;?></b> eposta hesabının, <b><?=$yuinEmailPassMasked;?></b> şifresi ile <b><?=$yuinEmailSMTP;?></b> SMTP sunucusu üzerinden gönderilecektir. Eğer bilgiler güncel değilse <b>protected/protected_constants.php</b> dosyasından güncelleyiniz!</h6>
				        <div class="col-lg-6">    
				            <div style="display: none;" id="epostaGonderiWarningTag" class="alert alert-warning">
                                <p id="mailAlertBox">Tüm katılımcılara eposta gönderiliyor. Her eposta 5 saniyede bir gönderilir. Lütfen hedef sayıya ulaşana kadar sayfadan ayrılmayınız.<br><b>Bu işlem esnasında tarayıcınız 5 saniye kadar süreyle donabilir, lütfen sayfayı terk etmeyin ya da durdurmayın.</b><br></p>
                            </div>
                        </div>
				        <div class="col-lg-6">
				            <label for="recipient"><p>Alıcılar</p></label><br>
					        <input type="text" id="recipient" name="recp" value="" placeholder="Eposta alıcıları" required>
				        </div>
				        <div class="col-lg-6">
				            <label for="title"><p>Eposta konusu</p></label><br>
					        <input type="text" id="title" name="title" value="" placeholder="Eposta konusu" required>
				        </div>
				        <div class="col-lg-6">
				            <label for="contentt"><p>Eposta içeriği <b>(HTML tagleri kullanabilirsiniz)</b></p></label><br>
					        <textarea id="contentt" name="contentt" required>


<pre><i>____________________________________
 
 Sevgilerimizle,
Yeditepe Üniversitesi Bilişim Kulübü 

https://yuin.yeditepe.edu.tr
https://www.instagram.com/YuInformatics
https://www.linkedin.com/in/YuBilisimKulubu
https://www.twitter.com/YuInformatics</i></pre>
					        </textarea>
				        </div>
				        
				        <br>
				    </form>
				    <button id="invokejQryBtn" style="display: none;" class="site-btn" onclick="sendEmailTekerTeker()">Eposta Gönder</button>
				    <button id="showFormsBtn" class="site-btn" onclick="showForms()">Katılımcılara EPosta Gönder</button>
				    </center>
				    <table>
				    <tr>
				        <td>ID</td>
				        <td>Geldi mi?</td>
				        <td>Öğrenci No.</td>
				        <td>İsim</td>
				        <td>Soyisim</td>
				        <td>Fakülte</td>
				        <td>Bölüm</td>
				        <td>Yetki seviyesi</td>
				        <td>Eposta</td>
				        <td>Etkinliklerden haber ver</td>
				        <td>Telefon numarası</td>
				        <td>Katılım tarihi</td>
				    </tr>
				    
				    <?php
				        
				        foreach($acttt as $user) {
				            
				    ?>
				    
				    <script>
				    var recipients = document.getElementById("recipient").value
				    recipients = recipients + ", <?=$user['email'];?>";
				    document.getElementById("recipient").value = recipients;
				    </script>
				    
				    <tr>
				        <td><?=$user['uid'];?></td>
				        <td>-</td>
				        <td><?=$user['user'];?></td>
				        <td><?=$user['name'];?></td>
				        <td><?=$user['surname'];?></td>
				        <td><?=$user['faculty'];?></td>
				        <td><?=$user['department'];?></td>
				        <td><?=$user['permlevel'];?></td>
				        <td><?=$user['email'];?></td>
				        <td><?=$user['activityMailOpt'];?></td>
				        <td><?=$user['phonenum'];?></td>
				        <td><?=$user['created_at'];?></td>
				    </tr>
				    
				    <?php
				        }
				    ?>
				    </table>
				    <?php
				}
				?>
				    
				
				<hr>
				<center>
				<h3>Yeni etkinlik ekle</h3>
				<p>Aşağıdan yeni etkinlik ekleyebilirsiniz</p>
				<?php
			        
			        if(isset($error)) {
			        ?>
			        <p style="color:red;"><?=$error;?></p>
			        <?php
			        }
			        ?>
				<form class="comment-form --contact" method="post" enctype="multipart/form-data">
			        
			        <input type="hidden" name="action" value="addAct">
			        <center>
				    <div class="col-lg-6">
				        <label for="banner"><p>Etkinlik afişi</p></label><br>
					    <input type="file" id="banner" name="banner" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="tag"><p>Etkinlik etiketi <b>(Afişin altındaki mavi kutucuk)</b></p></label>
					    <input type="text" id="tag" name="tag" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="info"><p>Etkinlik ismi</p></label>
					    <input type="text" id="info" name="info" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="location"><p>Etkinlik konumu</p></label>
					    <input type="text" id="location" name="location" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="date"><p>Etkinlik tarihi</p></label>
					    <input type="date" id="date" name="date" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="datetime"><p>Etkinlik saati</p></label>
					    <input type="time" id="datetime" name="datetime" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="slots"><p>Etkinlik kontenjanı</p></label>
					    <input type="text" id="slots" name="slots" value="" required>
				    </div>
				    <div class="col-lg-6">
				        <button class="site-btn">Etkinlik ekle</button>
				    </div>
			    </form>
		        </center>
	</section>
	<!-- Courses section end-->
	
	<script>
        /* YUIN Etkinliklerinin afişlerini asyncB64Loader.php API noktası ile afiş img taglarının src adreslerine at. */
        
        window.onload = function() {
            
            const afisLinkJSON = '<?=$afisLink;?>';
            const afisLinkList = JSON.parse(afisLinkJSON);
            
            var afisId;
            var afisLink;
            
            Object.entries(afisLinkList).forEach(item => {
                afisId = item[0];
                afisLink = item[1];
                document.getElementById(afisId).src = afisLink;
            });
        }
    </script>

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