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
}

if($bilgi['permlevel'] < 1) {
    
    unset($stmt);
    unset($pdo);
    header('Location: logout.php');
    exit;
}

if(!isset($_GET['admSpecificSubgroup'])) {

    if($stmt = $pdo->prepare("SELECT * FROM subgroups")) {
        
        if($stmt->execute()) {
            
            $subgroups = $stmt->fetchAll();
        }else{
            exit;
        }
    }
}else{
    
    if(is_numeric($_GET['admSpecificSubgroup'])) {
        
        $subgroupId = trim($_GET['admSpecificSubgroup']);
        if($stmt = $pdo->prepare("SELECT * FROM subgroups WHERE id = :id")) {
            
            $stmt->bindParam(":id", $subgroupId, PDO::PARAM_STR);
            if($stmt->execute()) {
                
                $subgroups = $stmt->fetch();
            }else{
                exit;
            }
        }
    }else{
        
        unset($stmt);
        unset($pdo);
        header('Location: subgroupsAdministration.php');
        exit;
    }
}

$plb = 1;
$pli = 2;

if($stmt = $pdo->prepare("SELECT uid, user, name, surname FROM users WHERE permlevel = :plb OR permlevel = :pli")) {
    
    $stmt->bindParam(":plb", $plb, PDO::PARAM_STR);
    $stmt->bindParam(":pli", $pli, PDO::PARAM_STR);
    
    if($stmt->execute()) {
        
        $admins = $stmt->fetchAll();
    }else{
        exit;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    
    if($action == 'editSubgroup' && isset($_GET['admSpecificSubgroup']) && !empty($_GET['admSpecificSubgroup']) && is_numeric($_GET['admSpecificSubgroup'])) {
        
        $error = '';
        $specificSubg = $_GET['admSpecificSubgroup'];
        
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
            
            /*if(!empty($banner)) {
                
                //$error .= "Afiş boş olamaz!" . PHP_EOL;
                $updateBanner = true;
            }*/
            
            $photo = base64_encode($photo);
            unlink($_FILES['photo']['tmp_name']);
            $photo = 'data:' . $photoExt . ';base64,' . $photo;
        }
        
        if($bilgi['permlevel'] < 2) {
            
            $adminInfo = '';
            if($stmt = $pdo->prepare("SELECT admin FROM subgroups WHERE id = :id")) {
                
                $stmt->bindParam(":id", $specificSubg, PDO::PARAM_STR);
                if($stmt->execute()) {
                    
                    $adminInfo = $stmt->fetch();
                }
            }
            
            if(!is_array($adminInfo)) {
                
                $error .= "Bağlantı hatası! Lütfen tekrar deneyin." . PHP_EOL;
            }
            
            $isAdmin = false;
            foreach($adminInfo as $dP) {
                
                if($dP['admin'] == $bilgi['uid']) {
                    
                    $isAdmin = true;
                }
            }
            
            if($isAdmin == false) {
                
                $error .= "Bu işlemi yapmak için yeterli yetkilere sahip değilsiniz!" . PHP_EOL;
            }
        }
        
        $name = trim($_POST['name']);
        $topic = trim($_POST['topic']);
        $html = $_POST['html'];
        $admin = trim($_POST['admin']);
        
        if(empty($name)) {
            
            $error .= "Alt grup ismi boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($topic)) {
            
            $error .= "Alt grup konusu boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($html)) {
            
            $error .= "Alt grubun HTML kodu boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($admin) && $bilgi['permlevel'] > 1) {
            
            $error .= "Alt grup sorumlusu boş bırakılamaz!" . PHP_EOL;
        }
        
        $sqlqry = '';
        
        if($bilgi['permlevel'] > 1 && $updatePhoto == false) {
            
            $sqlqry = "UPDATE subgroups SET name = :name, topic = :topic, html = :html, admin = :admin WHERE id = :id";
        }else if($bilgi['permlevel'] > 1 && $updatePhoto == true) {
            
            $sqlqry = "UPDATE subgroups SET banner = :photo, name = :name, topic = :topic, html = :html, admin = :admin WHERE id = :id";
        }else if($bilgi['permlevel'] < 2 && $updatePhoto == false) {
            
            $sqlqry = "UPDATE subgroups SET name = :name, topic = :topic, html = :html WHERE id = :id";
        }else if($bilgi['permlevel'] < 2 && $updatePhoto == true) {
            
            $sqlqry = "UPDATE subgroups SET banner = :photo, name = :name, topic = :topic, html = :html WHERE id = :id";
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare($sqlqry)) {
                
                if($bilgi['permlevel'] > 1 && $updatePhoto == false) {
                    
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
                    $stmt->bindParam(":html", $html, PDO::PARAM_STR);
                    $stmt->bindParam(":admin", $admin, PDO::PARAM_STR);
                    
                }else if($bilgi['permlevel'] > 1 && $updatePhoto == true) {
                    
                    $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
                    $stmt->bindParam(":html", $html, PDO::PARAM_STR);
                        $stmt->bindParam(":admin", $admin, PDO::PARAM_STR);
                    
                }else if($bilgi['permlevel'] < 2 && $updatePhoto == false) {
                    
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
                    $stmt->bindParam(":html", $html, PDO::PARAM_STR);
                        
                }else if($bilgi['permlevel'] < 2 && $updatePhoto == true) {
                        
                    $stmt->bindParam(":photo", $photo, PDO::PARAM_STR);
                    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                    $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
                    $stmt->bindParam(":html", $html, PDO::PARAM_STR);
                    
                }
                    
                $stmt->bindParam(":id", $specificSubg, PDO::PARAM_STR);
                if($stmt->execute()) {
                
                    unset($stmt);
                    unset($pdo);
                    header('Location: subgroupsAdministration.php');
                    exit;
                }else{
                    
                    unset($stmt);
                    unset($pdo);
                    die('Database Error occured please try again!');
                }
            }
        }
    }
    
    if($action == 'addSubgroup') {
        
        $error = '';
        
        $photo = @$_FILES['photo']['tmp_name'];
        if(isset($photo) && !empty($photo)) {
            
            $photoExt = $_FILES['photo']['type'];
            
            if($_FILES['photo']['size'] < 0 || !getimagesize($_FILES['photo']['tmp_name'])) {
                
                unlink($_FILES['photo']['tmp_name']);
                $error .= "Lütfen fotoğraf yükleyin. Gönderim yarıda mı kesildi de fotoğraf eksik ya da bozuk geldi?" . PHP_EOL;
            }
            
            $photo = file_get_contents($_FILES['photo']['tmp_name']);
            
            /*if(!empty($banner)) {
                
                //$error .= "Afiş boş olamaz!" . PHP_EOL;
                $updateBanner = true;
            }*/
            
            $photo = base64_encode($photo);
            unlink($_FILES['photo']['tmp_name']);
            $photo = 'data:' . $photoExt . ';base64,' . $photo;
        }else{
            
            $error .= "Lütfen fotoğraf yükleyiniz." . PHP_EOL;
        }
        
        if($bilgi['permlevel'] < 2) {
            
            $error .= "Üzgünüm, bu işlemi gerçekleştirmek için yeterli yetkilere sahip değilsiniz!" . PHP_EOL;
        }
        
        $name = trim($_POST['name']);
        $topic = trim($_POST['topic']);
        $html = $_POST['html'];
        $admin = trim($_POST['admin']);
        
        if(empty($name)) {
            
            $error .= "Alt grup ismi boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($topic)) {
            
            $error .= "Alt grup konusu boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($html)) {
            
            $error .= "Alt grubun HTML kodu boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($admin)) {
            
            $error .= "Alt grup sorumlusu boş bırakılamaz!" . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("INSERT into subgroups (banner, name, topic, html, admin) VALUES (:banner, :name, :topic, :html, :admin)")) {
                
                $stmt->bindParam(":banner", $photo, PDO::PARAM_STR);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":topic", $topic, PDO::PARAM_STR);
                $stmt->bindParam(":html", $html, PDO::PARAM_STR);
                $stmt->bindParam(":admin", $admin, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    header('Location: subgroupsAdministration.php');
                    exit;
                }else{
                    
                    unset($stmt);
                    unset($pdo);
                    die('Database Error occured please try again!');
                }
            }
        }
    }
    
    if($action == 'deleteSubgroup' && isset($_GET['admSpecificSubgroup']) && !empty($_GET['admSpecificSubgroup']) && is_numeric($_GET['admSpecificSubgroup'])) {
        
        $specificSubg = $_GET['admSpecificSubgroup'];
        
        if($bilgi['permlevel'] < 2) {
            
            unset($stmt);
            unset($pdo);
            header('Location: subgroupsAdministration.php?accessDenied');
            exit;
        }
        
        if($stmt = $pdo->prepare("DELETE from subgroups WHERE id = :id")) {
            
            $stmt->bindParam(":id", $specificSubg, PDO::PARAM_STR);
            if($stmt->execute()) {
                
                unset($stmt);
                unset($pdo);
                header('Location: subgroupsAdministration.php');
                exit;
            }else{
                
                unset($stmt);
                unset($pdo);
                die('Database Error occured please try again!');
            }
        }
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
			<span>Alt grupları yönet</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		<div class="container">
			
				<div class="section-title text-center">
					<h3>Alt grupları yönet</h3>
					
				</div>
				<center>
				<?php
				
				if(!isset($_GET['admSpecificSubgroup'])) {
				    
				    ?>
				    
				    <table>
				        <tr>
				            <td>ID</td>
				            <td>Afiş</td>
				            <td>İsim</td>
				            <td>Konu</td>
				            <td>Yönetici <b>(ID)</b></td>
				            <td>Yönet</td>
				        </tr>
				    <?php
				    
				    foreach($subgroups as $subgroup) {
				        
				        ?>
				        
				        <tr>
				            <td><?=$subgroup['id'];?></td>
				            <td><img src="<?=$subgroup['banner'];?>" style="width:10%;height:10%;"></td>
				            <td><?=$subgroup['name'];?></td>
				            <td><?=$subgroup['topic'];?></td>
				            <td><?=$subgroup['admin'];?></td>
				            <td><a href="subgroupsAdministration.php?admSpecificSubgroup=<?=$subgroup['id'];?>"><button class="site-btn">YÖNET</button></a></td>
				        </tr>
				        
				        <?php
				    }
				    
				    ?>
				    </table>
				    <?php
				}else{
				    
				    ?>
				    <h5>Spesifik bir alt grubu yönet</h5>
				    <p>Alt grup numarası <?=htmlspecialchars($_GET['admSpecificSubgroup']);?></p>
				    <hr>
				    <form method="post">
				        <input type="hidden" name="action" value="deleteSubgroup">
				    <?php
			            if($bilgi['permlevel'] > 1) {
			            ?>
			            <button class="site-btn" style="background: #b00c00;"><i class="far fa-trash-alt"></i> Alt grubu sil</button><br><br>
			            <?php
			            }
			        ?>
			        </form>
				    <form class="comment-form --contact" method="post" enctype="multipart/form-data">
				        
				        <input type="hidden" name="action" value="editSubgroup">
				        <div class="col-lg-6">
				            <img src="<?=$subgroups['banner'];?>" style="width:30%;height:30%;"><br><br>
				            <label for="galPhoto"><p>Fotoğraf<br><b>NOT: Yüksek çözünürlüklü fotoğraflar doğru yüklenmeyebilir.</b></p></label>
					        <input type="file" id="galPhoto" name="photo">
				        </div>
			            <div class="col-lg-6">
			                <label for="name"><p>Alt grup ismi</p></label>
			                <input type="text" id="name" name="name" value="<?=$subgroups['name'];?>" required>
			            </div>
			            <div class="col-lg-6">
			                <label for="topic"><p>Alt grup konusu</p></label>
			                <input type="text" id="topic" name="topic" value="<?=$subgroups['topic'];?>" required>
			            </div>
			            <div class="col-lg-6">
			                <label for="html"><p><b>Alt grup sayfa HTML kodu</b></p></label>
			                <h5><b>HTML SABİTLERİ LEJANTI</b></h5>
			                <p>Alttaki değişkenleri HTML kodu içerisinde kullanabilirsiniz. Bu değişkenler uygun değerleri sağlayacaklardır.</p>
			                <br>
			                <p><b>%bannerSrc%</b> - Base64 formatında alt grup afişini verir. <br>Örnek kullanım: <?php echo htmlspecialchars('<img src="%bannerSrc%">');?></p><br>
			                <p><b>%name%</b> - Alt grup ismini verir. Örnek kullanım: <?php echo htmlspecialchars('<p>%name%</p>');?></p><br>
			                <p><b>%topic%</b> - Alt grup açıklamasını verir.</p><br>
			                <p><b>%email%</b> - Alt grup yetkilisinin eposta hesabını verir.</p><br>
			                <p><b>%phone%</b> - Alt grup yetkilisinin telefon numarasını verir.</p><br>
			                <p><b>%username%</b> - Alt grup yetkilisinin ismini verir.</p><br>
			                <p><b>%usersurname%</b> - Alt grup yetkilisinin soyismini verir.</p><br>
			                <textarea id="html" name="html" required><?=$subgroups['html'];?></textarea>
			            </div>
			            <?php
			            if($bilgi['permlevel'] > 1) {
			            ?>
			            <div class="col-lg-6">
			                <label for="admin"><p>Alt grup yetkilisi</p></label>
			                <select id="admin" name="admin">
			                <?php
			                
			                foreach($admins as $admin) {
			                    
			                    ?>
			                    <option value="<?=$admin['uid'];?>"><?=$admin['uid'] . ' | ' . $admin['user'] . ' ' . $admin['name'] . ' ' . $admin['surname'];?></option>
			                    <?php
			                }
			                ?>
			                </select>
			            </div>
			            <?php
			            }
			            ?>
			            <div class="col-lg-6">
				            <button class="site-btn">Güncelle</button>
				        </div>
				    </form>
				    
				    <?php
				}
				
				?>
				<hr>
				<h3>Alt grup ekle</h3>
			    <form class="comment-form --contact" method="post" enctype="multipart/form-data">
			        
			        <input type="hidden" name="action" value="addSubgroup">
			        <div class="col-lg-6">
				        <label for="galPhoto"><p>Fotoğraf</p></label>
					    <input type="file" id="galPhoto" name="photo" required>
				    </div>
			        <div class="col-lg-6">
			            <label for="name"><p>Alt grup ismi</p></label>
			            <input type="text" id="name" name="name" value="" required>
			        </div>
			        <div class="col-lg-6">
			            <label for="topic"><p>Alt grup konusu</p></label>
			            <input type="text" id="topic" name="topic" value="" required>
			        </div>
			        <div class="col-lg-6">
			                <label for="html"><p>Alt grup sayfa HTML kodu</p></label>
			                <textarea id="html" name="html" required></textarea>
			            </div>
			        <div class="col-lg-6">
			            <label for="admin"><p>Alt grup yetkilisi</p></label>
			            <select id="admin" name="admin">
			                <?php
			                
			                foreach($admins as $admin) {
			                    
			                    ?>
			                    <option value="<?=$admin['uid'];?>"><?=$admin['uid'] . ' | ' . $admin['user'] . ' ' . $admin['name'] . ' ' . $admin['surname'];?></option>
			                    <?php
			                }
			                ?>
			            </select>
			        </div>
			        <div class="col-lg-6">
				        <button class="site-btn">Ekle</button>
				    </div>
			    </form>
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