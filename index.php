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

/*

Bu dosya tekrardan revize edildi. Eski hali vasattı.
Yine de merakına yenik düşenler VEYA PHP ile websitesi nasıl yazılmamalı görmek isteyenler için eski dosyayı silmedim.
Girin ve kod NASIL YAZILMAMALI görün.

ve evet. Şaheser bana ait. Eski dosyada siteye 4 kişi girince çöküyordu.

*/

session_start();

$baslangic = time();

define('YUIN',1);

// Veritabanınla bağlantı kur ve basit ayarları uygula (mesela debugging)
include "/home/yuinyeditepe/public_html/backend/connect.php";

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";

// Yeni algoritma etkinlik getirme başlangıç. Bunu eklemem sonucu adam oldu site.

$kacTane = 3;
if(isset($_GET['etkinlikGetir']) && is_numeric($_GET['etkinlikGetir'])) {
    
    $kacTane = (int)$_GET['etkinlikGetir'];
}

if(!is_numeric($kacTane) || !is_int($kacTane) || $kacTane > 12 || $kacTane < 3) {
    
    $kacTane = 3;
}

$time = time();

// Banner artık burada çekilmeyecek. Backend kısmına taşındı. Frontend üzerinden JS ile yükleme yapılacak.
$stmt = $pdo->prepare("SELECT id,tag,info,location,date,slots FROM etkinlik GROUP BY date DESC LIMIT :kacTane"); // GROUP BY date DESC Tarihe göre sıralar.
$stmt->bindParam(':kacTane', $kacTane, PDO::PARAM_INT);
$stmt->execute();
$etkinlikler = $stmt->fetchAll();

$availableActs = [];
$fullActs = [];
$expiredActs = [];

$afisLink = [];

foreach($etkinlikler as $kcts => $acts) {
    
    $thisId = $acts['id'];
    $afisLink['banner-' . $thisId] = 'backend/asyncB64Loader.php?eid=' . $thisId;
    
    if( ($acts['date'] - 1800) >= $time) {
        
        $stmt = $pdo->prepare("SELECT COUNT(uid) AS katilim FROM etkinlikKatilim WHERE eid = :id");
        $stmt->bindParam(":id", $thisId, PDO::PARAM_STR);
        $stmt->execute();
        $joinedCount = $stmt->fetch();
        $joinedCount = $joinedCount['katilim'];
        $etkinlikler[$kcts]['joined'] = $joinedCount;
        
        if($acts['slots'] <= $joinedCount) {
            
            $fullActs[] = $thisId;
            continue;
        }
        
        $availableActs[] = $thisId;
    }else{
        
        $expiredActs[] = $thisId;
        continue;
    }
}

// Asenkronize yükleme için afiş linklerini JSON'a çevir. Frontend kısmına aktarılacak.
$afisLink = json_encode($afisLink);

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

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $act = $_POST['action'];
    if($act == 'joinAct') {
        
        if($login == 0) {
            
            header('Location: login.php?loginToProceed');
            exit;
        }
        
        $actError = '';
        
        $activity = $_POST['eid'];
        if(empty($activity)) {
            
            $actError .= "Üzgünüm! Geçerli bir veri gelmedi." . PHP_EOL;
        }
        
        if(!in_array($activity, $availableActs)) {
            
            $actError .= "Üzgünüm! Bu etkinlik katılıma uygun değil." . PHP_EOL;
        }
        
        if($stmt = $pdo->prepare("SELECT * FROM etkinlikKatilim WHERE uid = :uid AND eid = :eid")) {
            
            $stmt->bindParam(":eid", $activity, PDO::PARAM_STR);
            $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
            
            if($stmt->execute()) {
                
                if($stmt->rowCount() > 0) {
                
                    $actError .= "Bu etkinliğe zaten kaydoldunuz." . PHP_EOL;
                }
            }else{
                
                $actError .= "Üzgünüm! (2) Beklenmedik bir hata gerçekleştiğinden seni bu etkinliğe kaydedemedik. Lütfen daha sonra tekrardan dene." . PHP_EOL;
            }
        }else{
            
            $actError .= "Üzgünüm! (1) Beklenmedik bir hata gerçekleştiğinden seni bu etkinliğe kaydedemedik. Lütfen daha sonra tekrardan dene." . PHP_EOL;
        }
        
        if(empty($actError)) {
            
            if($stmt = $pdo->prepare("INSERT into etkinlikKatilim (eid,uid) VALUES (:eid,:uid)")) {
                
                $stmt->bindParam(":eid", $activity, PDO::PARAM_STR);
                $stmt->bindParam(":uid", $_SESSION['uid'], PDO::PARAM_STR);
                
                if($stmt->execute()) {
                    
                    unset($stmt);
                    unset($pdo);
                    header('Location: index.php?joinAct=' . $activity);
                    exit;
                    
                }else{
                    
                    $actError .= "Beklenmedik bir sistem hatası meydana geldi! (2) Lütfen daha sonra tekrar deneyin. Sorun devam ederse yönetim kadrosundan birisine bu durumu bildirin." . PHP_EOL;
                }
            }else{
                
                $actError .= "Beklenmedik bir sistem hatası meydana geldi! (1) Lütfen daha sonra tekrar deneyin. Sorun devam ederse yönetim kadrosundan birisine bu durumu bildirin." . PHP_EOL;
            }
        }
    }
}

unset($stmt);
unset($pdo);

// İlginç bir bilgi çek
include_once '/home/yuinyeditepe/public_html/backend/didyouknowtr.php';
if(function_exists('didYouKnow')) {
    
    $dyk = didYouKnow();
}else{
    
    $dyk = 'Normal şartlarda burada size ilginç bilgiler vermemiz gerekmekteydi. Fakat her nedense PHP, bu işlemi yapan fonksiyonu bulamadığından yapamadık. Sayfayı yenileyip tekrar deneyebilirsiniz.';
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<title>Yeditepe Üniversitesi Bilişim Kulübü | Ana Sayfa</title>
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
	
	<!-- Navbar alanı  -->
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
	<!-- Navbar alanı bitiş -->
	
	<?php
	
	if($login == 1) {
	
	?>
	<!-- Ek menü  -->
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
	<!-- Ek menü bitiş -->
	<?php
	
	}
	
	?>
	
	<!-- Dergilik -->
	<section class="fact-section spad set-bg" data-setbg="img/fact-bg.jpg">
		<div class="container">
			<h3 style="color:white;">YUIN Dergi</h3>
			<p style="color:white;">Kulübümüz tarafından çıkartılan dergimize buradan ulaşabilirsiniz</p>
			<a class="site-btn" href="magazine.php">Dergileri gör</a>
		</div>
	</section>
	<!-- Dergilik bitiş -->
	
	<?php
	if($login) {
	?>
	<!-- Enteresan Bilgiler -->
	<div class="alert alert-light">
        <center><strong>Bunu biliyor muydunuz?</strong> <?=$dyk;?> <a onclick="window.location.reload(0);"><b style="color: blue;">Başka neyi bilmiyorum?</b></a></center>
    </div>
    <!-- Enteresan Bilgiler bitiş -->
    <?php
	}
	?>
    
	<!-- Courses section -->
	<section class="courses-section">
		<div class="container">
			<div class="section-title text-center">
			    <br>
			    <?php
			    
			    if(isset($_GET['joinAct'])) {
			        
			        ?>
			        <div class="alert alert-success">
                        
                        <strong><i class="fas fa-check"></i> Başarılı!</strong> Etkinliğe katılım başarılı oldu! Katıldığınız tüm etkinlikleri "Etkinliklerim" sayfasında görebilirsiniz.
                    </div>
			        <?php
			    }
			    
			    if(isset($_GET['logout'])) {
			        
			        ?>
			        <div class="alert alert-success">
                        
                        <strong><i class="fas fa-check"></i> Başarılı!</strong> Başarıyla YUIN Club'dan çıkış yaptınız. Güle güle.
                    </div>
			        <?php
			    }
			    
			    ?>
			    <br>
				<h3>Son zamanlardaki etkinliklerimiz</h3>
				<?php
				
				if(isset($actError)) {
				?>
				<h6 style="color:red;"><?=$actError;?></h6>
				<?php
				}
				?>
			</div>
			<div class="row">
			    
			    <script>
			        
			        function activityFull() {
			            
			            alert("ÜZGÜNÜZ!\n\nKatılmak istediğiniz etkinliğin kotası dolmuş durumda :( Eğer yine de katılmak isterseniz Yönetim Kadrosu üyelerine ulaşıp etkinliğin kotasının arttırılmasını talep etmeyi deneyebilirsiniz.");
			        }
			        
			        function activityExpired() {
			            
			            alert("ÜZGÜNÜZ!\n\nKatılmak istediğiniz etkinliğin süresi geçmiş. Süresi geçmeyen etkinliklerimize katılabilirsiniz.\n");
			        }
			    </script>
			    <?php
			    
			    // PHP Başlangıcı
			    // Etkinlik afişlerini göster
			    
			    foreach($etkinlikler as $etkinlik) {
			        
			        $etkSaat = date('H:i', $etkinlik['date']);
			        $etkTarih = date('d/m/Y', $etkinlik['date']);
			        
			        ?>
			        
			        <!-- course item -->
				<div class="col-lg-4 col-md-6 course-item">
					<div class="course-thumb">
						<img id="banner-<?=$etkinlik['id'];?>" src="img/loading.gif">
						<br><br>
						<div class="course-cat">
							<span><?=$etkinlik['tag'];?></span>
						</div>
					</div>
					<div class="course-info">
						<h4><?=$etkinlik['info'];?></h4><br>
						<div class="date"><i class="fa fa-clock-o"></i> Etkinlik tarihi:<?="\t" . $etkTarih;?></div>
						<div class="date"><i class="fa fa-clock-o"></i> Etkinlik saati:<?="\t" . $etkSaat;?></div>
						<div class="date"><i class="fas fa-map-marker-alt"></i> Etkinlik konumu: <?="\t" . $etkinlik['location'];?></div>
						<div class="date"><i class="fas fa-chair"></i> Etkinlik kontenjanı: <?="\t" . $etkinlik['slots'];?></div>
						<div class="date"><i class="fas fa-user"></i> Katılan üye sayısı: 
						<?php
						    
						    if(in_array($etkinlik['id'], $fullActs)) {
						        //Aynı zamanda üstte katılımcı sayısınıda göster
						        echo "\t" . $etkinlik['slots'] . '</div>';
						    ?>
						    <button class="site-btn" style="background: #b00c00;" onclick="activityFull()">KATIL</button>
						    <?php
						    }else if(in_array($etkinlik['id'], $expiredActs)){
						        //Aynı zamanda üstte katılımcı sayısınıda göster
						        echo "\t" . $etkinlik['slots'] . '</div>';
						    ?>
						    <button class="site-btn" style="background: #2f3652;" onclick="activityExpired()">KATIL</button>
						    <?php
						    }else{
						        //Aynı zamanda üstte katılımcı sayısınıda göster
						        echo "\t" . $etkinlik['joined'] . '</div>';
						    ?>
						    <form method="post">
						        
						        <input type="hidden" name="action" value="joinAct">
						        <input type="hidden" name="eid" value="<?=$etkinlik['id'];?>">
						        <button class="site-btn">KATIL</button>
						        
						    </form>
						    <?php
						    }
						    ?>
					</div>
				</div>
			        
			        <?php
			    }
			    
			    ?>
			    
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
				
			</div>
		</div>
		<br>
		<center>
		    <p><i>Birçok etkinlik arasından <?=htmlspecialchars($kacTane);?> adet etkinlik listelendi.</i><br><a href="?etkinlikGetir=<?=$kacTane+3;?>">Daha fazla etkinlik göster</a> | <a href="?etkinlikGetir=<?=$kacTane-3;?>">Daha az etkinlik göster</a></p>
		    <br>
		    <p><i>Bu sayfa <?=time()-$baslangic;?> saniyede <?=gethostname();?> sunucusu üzerinde size özel olarak oluşturuldu.</i></p>
		</center>
	</section>
	<!-- Courses section end-->


	<!-- Fact section
	<section class="fact-section spad set-bg" data-setbg="img/fact-bg.jpg">
		<div class="container">
			<div class="row">
				<div class="col-sm-6 col-lg-3 fact">
					<div class="fact-icon">
						<i class="ti-crown"></i>
					</div>
					<div class="fact-text">
						<h2>50</h2>
						<p>YEARS</p>
					</div>
				</div>
				
				<div class="col-sm-6 col-lg-3 fact">
					<div class="fact-icon">
						<i class="ti-user"></i>
					</div>
					<div class="fact-text">
						<h2>500</h2>
						<p>STUDENTS</p>
					</div>
				</div>
				<div class="col-sm-6 col-lg-3 fact">
					<div class="fact-icon">
						<i class="ti-pencil-alt"></i>
					</div>
					<div class="fact-text">
						<h2>800+</h2>
						<p>LESSONS</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Fact section end-->
	
	<!-- Dergilik eskiden buradaydı 16.11.2020 tarihinde alınan kararla yukarı taşındı. -->
	
	<!-- Event section
	<section class="event-section spad">
		<div class="container">
			<div class="section-title text-center">
				<h3>OUR EVENTS</h3>
				<p>Our department  initiated a series of events</p>
			</div>
			<div class="row">
				<div class="col-md-6 event-item">
					<div class="event-thumb">
						<img src="img/event/1.jpg" alt="">
						<div class="event-date">
							<span>24 Mar 2018</span>
						</div>
					</div>
					<div class="event-info">
						<h4>The dos and don'ts of writing a personal<br>statement for languages</h4>
						<p><i class="fa fa-calendar-o"></i> 08:00 AM - 10:00 AM <i class="fa fa-map-marker"></i> Center Building, Block A</p>
						<a href="" class="event-readmore">REGISTER <i class="fa fa-angle-double-right"></i></a>
					</div>
				</div>
				<div class="col-md-6 event-item">
					<div class="event-thumb">
						<img src="img/event/2.jpg" alt="">
						<div class="event-date">
							<span>22 Mar 2018</span>
						</div>
					</div>
					<div class="event-info">
						<h4>University interview tips:<br>confidence won't make up for flannel</h4>
						<p><i class="fa fa-calendar-o"></i> 08:00 AM - 10:00 AM <i class="fa fa-map-marker"></i> Center Building, Block A</p>
						<a href="" class="event-readmore">REGISTER <i class="fa fa-angle-double-right"></i></a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- Event section end -->

	<!-- Newsletter section
	<section class="newsletter-section">
		<div class="container">
			<div class="row">
				<div class="col-md-5 col-lg-7">
					<div class="section-title mb-md-0">
					<h3>NEWSLETTER</h3>
					<p>Subscribe and get the latest news and useful tips, advice and best offer.</p>
				</div>
				</div>
				<div class="col-md-7 col-lg-5">
					<form class="newsletter">
						<input type="text" placeholder="Enter your email">
						<button class="site-btn">SUBSCRIBE</button>
					</form>
				</div>
			</div>
		</div>
	</section>
	<!-- Newsletter section end -->	
    
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