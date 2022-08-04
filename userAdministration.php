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

$specificUser = 0;

if(isset($_GET['administrateUser']) && !empty($_GET['administrateUser']) && is_numeric($_GET['administrateUser'])) {
    
    $userToAdmin = $_GET['administrateUser'];
    $specificUser = 1;
    
    if($stmt = $pdo->prepare("SELECT * FROM users WHERE uid = :uid")) {
        
        $stmt->bindParam(":uid", $userToAdmin, PDO::PARAM_STR);
        if($stmt->execute()) {
            
            $users = $stmt->fetch();
        }
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $error = '';
        $name = trim($_POST['name']);
        $surname = trim($_POST['surname']);
        $user = trim($_POST['user']);
        $faculty = trim($_POST['faculty']);
        $department = trim($_POST['department']);
        $permlevel = trim($_POST['permlevel']);
        if(isset($_POST['pass']) && !empty($_POST['pass'])) {
        
            $pass = $_POST['pass'];
            $passv = $_POST['passverifi'];
            
            if($pass != $passv) {
                
                $error .= "Girdiğiniz şifreler uyuşmuyor! Lütfen yeniden deneyin." . PHP_EOL;
            }
            
            $pass = password_hash($pass, PASSWORD_DEFAULT);
            
        }else{
            
            $pass = $users['pass'];
        }
        
        $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
        $surname = filter_var($surname, FILTER_SANITIZE_SPECIAL_CHARS);
        $department = filter_var($department, FILTER_SANITIZE_SPECIAL_CHARS);
        
        $phonenum = trim($_POST['phonenum']);
        $phonenum = filter_var($phonenum, FILTER_SANITIZE_SPECIAL_CHARS);
        
        $amo = 0;
        if(isset($_POST['activityMailOpt'])) {
            
            $amo = 1;
        }
        
        $status = 0;
        if(isset($_POST['accountStatus'])) {
            
            $status = 1;
        }
        
        $apia = 0;
        if(isset($_POST['apiAccessAllowed']) && $_POST['apiAccessAllowed'] == 1) {
            
            $apia = 1;
        }
        
        $email = trim($_POST['email']);
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            
            $error .= "Eposta adresiniz geçersiz!" . PHP_EOL;
        }
        
        if(empty($user)) {
            
            $error .= "Öğrenci numarası boş olamaz!" . PHP_EOL;
        }
        
        if(empty($name)) {
            
            $error .= "İsim boş olamaz!" . PHP_EOL;
        }
        
        if(empty($surname)) {
            
            $error .= "Soyisim boş olamaz!" . PHP_EOL;
        }
        
        if(empty($permlevel) && $permlevel != 0) {
            
            $error .= "Yetki seviyesi boş olamaz!" . PHP_EOL;
        }
        
        if($permlevel == '-') {
            
            $permlevel = $users['permlevel'];
        }
        
        if($permlevel != $users['permlevel']) {
            
            if($permlevel != 2 && $users['permlevel'] == 2) {
                
                if($stmt = $pdo->prepare("DELETE from adminInfo WHERE id = :uid")) {
                    
                    $stmt->bindParam(":uid", $userToAdmin, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }else if($permlevel == 2 && $users['permlevel'] != 2) {
                
                $defPriority = 70;
                $defPhoto = 'data:image/png;base64,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
';
                if($stmt = $pdo->prepare("INSERT into adminInfo (id, photo, priority) VALUES (:uid, :photo, :priority)")) {
                    
                    $stmt->bindParam(":uid", $userToAdmin, PDO::PARAM_STR);
                    $stmt->bindParam(":photo", $defPhoto, PDO::PARAM_STR);
                    $stmt->bindParam(":priority", $defPriority, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }
        
        if(empty($faculty)) {
            
            $error .= "Fakülte bilgisi boş olamaz!" . PHP_EOL;
        }
        
        if(empty($department)) {
            
            $error .= "Bölüm adı boş olamaz!" . PHP_EOL;
        }
        
        if(empty($phonenum)) {
            
            $error .= "Telefon numarası boş olamaz!" . PHP_EOL;
        }
        
        if(empty($email)) {
            
            $error .= "Eposta adresi boş olamaz!" . PHP_EOL;
        }
        
        if(empty($error)) {
            
            if($stmt = $pdo->prepare("UPDATE users SET user = :user, pass = :pass, name = :name, surname = :surname, permlevel = :permlevel, faculty = :faculty, department = :department, phonenum = :phonenum, email = :email, activityMailOpt = :amo, status = :status, isApiAllowed = :apia WHERE uid = :uid")) {
                
                $stmt->bindParam(":user", $user, PDO::PARAM_STR);
                $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":surname", $surname, PDO::PARAM_STR);
                $stmt->bindParam(":permlevel", $permlevel, PDO::PARAM_STR);
                $stmt->bindParam(":faculty", $faculty, PDO::PARAM_STR);
                $stmt->bindParam(":department", $department, PDO::PARAM_STR);
                $stmt->bindParam(":phonenum", $phonenum, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":amo", $amo, PDO::PARAM_STR);
                $stmt->bindParam(":apia", $apia, PDO::PARAM_STR);
                $stmt->bindParam(":status", $status, PDO::PARAM_STR);
                $stmt->bindParam(":uid", $userToAdmin, PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    
                    header('Location: userAdministration.php');
                    exit;
                }
            }
        }
    }
    
}else{
    
    if($stmt = $pdo->prepare("SELECT * FROM users")) {
        
        if($stmt->execute()) {
            
            $users = $stmt->fetchAll();
        }
    }
}

if(isset($_GET['excelExport'])) {
    
    // File Name & Content Header For Download
    $file_name = 'YuinExcelExport-' . time() . '.xls';
    header("Content-Disposition: attachment; filename=\"$file_name\"");
    header("Content-Type: application/vnd.ms-excel");
    
    if($stmt = $pdo->prepare("SELECT user, name, surname, faculty, department, email, phonenum, created_at FROM users")) {
    
        if($stmt->execute()) {
            
            $kayitli = $stmt->fetchAll();
            
            $result = [];
            $head = [];
            $head[] = 'User';
            $head[] = 'Name';
            $head[] = 'Surname';
            $head[] = 'Faculty';
            $head[] = 'Department';
            $head[] = 'Email';
            $head[] = 'Phonenum';
            $head[] = 'Created At';
            
            $result[] = $head;
            
            foreach($kayitli as $kayit) {
                
                $row = [];
                
                $row[] = $kayit['user'];
                $row[] = replace_tr($kayit['name']);
                $row[] = replace_tr($kayit['surname']);
                $row[] = replace_tr($kayit['faculty']);
                $row[] = replace_tr($kayit['department']);
                $row[] = $kayit['email'];
                $row[] = $kayit['phonenum'];
                $row[] = $kayit['created_at'];
                
                $result[] = $row;
            }
        }
    }
    
    function filterCustomerData(&$str) {
        
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if (strstr($str, '"'))
            $str = '"' . str_replace('"', '""', $str) . '"';
    }
    
    //To define column name in first row.
    $column_names = false;
    // run loop through each row in $customers_data
    foreach ($result as $row) {
        if (!$column_names) {
            
            echo implode("\t", array_keys($row)) . "\n";
            $column_names = true;
        }
        // The array_walk() function runs each array element in a user-defined function.
        array_walk($row, 'filterCustomerData');
        echo implode("\t", array_values($row)) . "\n";
    }

exit;
}

unset($stmt);
unset($pdo);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Üyeleri yönet</title>
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
			<span>Üyeleri yönet</span>
		</div>
	</div>
	<!-- Breadcrumb section end -->


	<!-- Courses section -->
	<section class="contact-page spad pt-0">
		
			
				<div class="section-title text-center">
					<h3>Üyeleri yönet</h3>
					
				</div>
				
				<?php
				
				// PHP Başlangıç
				
				if(isset($userToAdmin) && !empty($userToAdmin)) {
				
				?>
				
				<center>
			    <h3>Spesifik bir üyeyi yönet</h3>
			    <p>Üye numarası <?=$userToAdmin;?></p>
			    <?php
			    
			    if(isset($error)) {
			    ?>
			    <p style="color:red;"><?=$error;?></p>
			    <?php
			    }
			    ?>
			    </center>
			    
				<hr>
				<script>
				function genPass(length) {
                    
                    var result           = '';
                    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                    var charactersLength = characters.length;
                    for( var i = 0; i < length; i++ ) {
                        
                        result += characters.charAt(Math.floor(Math.random() * charactersLength));
                    }
                    
                    return result;
                }
				</script>
				<form method="post" class="comment-form --contact">
				    
				    <center>
				    <div class="col-lg-6">
				        <label for="user"><p>Kayıtlı öğrenci numarası</p></label>
					    <input type="text" id="user" name="user" value="<?=$users['user'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="name"><p>İsmi</p></label>
					    <input type="text" id="name" name="name" value="<?=$users['name'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="surname"><p>Soyismi</p></label>
					    <input type="text" id="surname" name="surname" value="<?=$users['surname'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="name"><p>Şifre <b>(Sadece şifreyi değiştirmek için girin, yoksa boş bırakın)</b></p></label>
					    <input type="password" id="pass" name="pass" value="">
				    </div>
				    <div class="col-lg-6">
				        <label for="name"><p>Şifreyi onaylama <b>(Üst kısım boş ise burayıda boş bırakın)</b></p></label>
					    <input type="text" id="passverifi" name="passverifi" value="">
					    <br>
					    <a class="site-btn" onclick="var pass = genPass(10);document.getElementById('pass').value = pass;document.getElementById('passverifi').value = pass;"><p style="color:white;">Rastgele şifre oluştur</p></a>
				    </div>
				    <br>
				    <div class="col-lg-6">
				        <label for="faculty"><p>Fakültesi</p></label>
					    <input type="text" id="faculty" name="faculty" value="<?=$users['faculty'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="permlevel"><p>Yetki seviyesi<br><b>Yetki seviyesi 0 - Normal kulüp üyesi, ekstra hiçbir yetki yok.<br>Yetki seviyesi 1 - Eğitimci yetki seviyesi. Kulübün alt gruplarında yönetici olabilir ve alt grup sayfalarını istediği gibi yönetebilir.<br>Yetki seviyesi 2 - YK yetki seviyesi. Tüm YUIN Club üzerinde yönetim yetkilerine sahip olur.</b></p></label>
					    <select id="permlevel" name="permlevel">
					        <option value="-">___ SEÇİNİZ ____</option>
					        <option value="0">0 - Normal kulüp üyesi</option>
					        <option value="1">1 - Eğitimci ya da grup başkanı</option>
					        <option value="2">2 - YK Üyesi</option>
					    </select>
					        
					    <!--<input type="text" id="faculty" name="permlevel" value="<?=$users['permlevel'];?>" required>-->
				    </div>
				    <div class="col-lg-6">
				        <label for="department"><p>Bölümü</p></label>
					    <input type="text" id="department" name="department" value="<?=$users['department'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="email"><p>Kayıtlı eposta adresi</p></label>
					    <input type="text" id="email" name="email" value="<?=$users['email'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="phonenum"><p>Kayıtlı telefon numarası</p></label>
					    <input type="text" id="phonenum" name="phonenum" value="<?=$users['phonenum'];?>" required>
				    </div>
				    <div class="col-lg-6">
				        <label for="activityMailOpt"><p>Bilişim Kulübü'nün düzenlediği etkinlikler ile ilgili eposta almak istiyor</p></label>
					    <?php
				    
				    if($users['activityMailOpt'] == 0) {
				        
				    ?>
				    <input type="checkbox" id="activityMailOpt" name="activityMailOpt" value="1"><br><br>
				    <?php
				    
				    }else{
				        
				    ?>
				    <input type="checkbox" id="activityMailOpt" name="activityMailOpt" value="0" checked><br><br>
				    <?php
				    
				    }
				    
				    ?>
				    </div>
				    <div class="col-lg-6">
				        <label for="apiAccessAllowed"><p>API Oauth erişim izni verilmesi YK tarafından kararlaştırıldı</p></label><br>
				        <?php
				        
				        if($users['isApiAllowed']) {
				            
				            ?>
				            <p style="color: red;"><b>API Aktif!</b></p>
				            <input type="checkbox" id="apiAccessAllowed" name="apiAccessAllowed" value="0"><br><br>
				            
				            <?php
				            
				        }else{
				            
				            ?>
				            <p style="color: green;"><b>API Devre dışı!</b></p>
				            <input type="checkbox" id="apiAccessAllowed" name="apiAccessAllowed" value="1"><br><br>
				            
				            <?php
				        }
				    ?>
				    </div>
				    <div class="col-lg-6">
				        <label for="phonenum"><p>Hesap durumu aktif mi? <b>(Tik yoksa üye yuin club'dan engellenir ve giriş yapamaz)</b></p></label>
					    <?php
				    
				    if($users['status'] == 0) {
				        
				    ?>
				    <input type="checkbox" id="accountStatus" name="accountStatus" value="1"><br><br>
				    <?php
				    
				    }else{
				        
				    ?>
				    <input type="checkbox" id="accountStatus" name="accountStatus" value="0" checked><br><br>
				    <?php
				    
				    }
				    
				    ?>
				    </div>
				</center>
				<div class="col-lg-12">
					<div class="text-center">
						<button class="site-btn">Üye bilgilerini güncelle</button>
					</div>
				</div>
				    
				</form>
				<?php
				
				}else{
				
				?>
				
				    <div class="col-lg-12">
					    <div class="text-center">
						    <a href="userAdministration.php?excelExport" target="__blank"><button class="site-btn"><i class="fas fa-file-excel"></i> Excel Tablosu Oluştur</button></a>
					    </div>
					</div>
			    
				</div>
				<table>
				    <tr>
				        <td>ID</td>
				        <td>Hesap durumu</td>
				        <td>API Erişimi</td>
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
				        <td>Yönet</td>
				    </tr>
				    
				    <?php
				    
				    foreach($users as $user) {
				        
				        ?>
				        
				        <tr>
				            <td><?=$user['uid'];?></td>
				            <td><?php
				                    if($user['status'] == 1) {
				                        
				                        echo '<p style="color:green;">AKTİF</p>';
				                    }else{
				                        
				                        echo '<p style="color:red;">KAPALI</p>';
				                    }
				                ?></td>
				            <td><?php
				                    if($user['isApiAllowed']) {
				                        
				                        echo '<p style="color:red;">AKTİF</p>';
				                    }else{
				                        
				                        echo '<p style="color:green;">KAPALI</p>';
				                    }
				                ?></td>
				            <td><?=$user['user'];?></td>
				            <td><?=$user['name'];?></td>
				            <td><?=$user['surname'];?></td>
				            <td><?=$user['faculty'];?></td>
				            <td><?=$user['department'];?></td>
				            <td><?=$user['permlevel'];?></td>
				            <td><?=$user['email'];?></td>
				            <td><?php if($user['activityMailOpt'] == 1): echo 'EVET'; else: echo 'HAYIR'; endif;?></td>
				            <td><?=$user['phonenum'];?></td>
				            <td><?=$user['created_at'];?></td>
				            <td><a href="userAdministration.php?administrateUser=<?=$user['uid'];?>"><button class="site-btn">YÖNET</button></a></td>
				        </tr>
				        
				        <?php
				        
				    }
				}
				    
				    ?>
				    
				</table>
			
		
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