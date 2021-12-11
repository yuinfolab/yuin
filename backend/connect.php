<?php

if(!defined("YUIN"))
    die("Güvenlik sebebiyle bu dosyaya direkt erişime izin verilmemektedir.");

// Bu dosyadaki gizli bilgiler PHP sabitleri ile korunmaktadır.
require_once "/home/yuinyeditepe/public_html/protected/protected_constants.php";

require_once "/home/yuinyeditepe/public_html/backend/yuinPass.php";

/* GENEL SİTE AYARLARI YÖNETİMİ */

$debugLevel = 1;

/* GENEL SİTE AYARLARI YÖNETİMİ BİTİŞİ */

if($debugLevel == 2) {
    
    ini_set('display_errors',1);
    error_reporting(E_ALL);
}else{
    
    error_reporting(0);
}

/*

7.12.2021 bot koruması devre dışı bırakıldı.

if(!validateYuinPass()) {
    
    header('Location: stairwayToYuin.php?hedef=' . $_SERVER['PHP_SELF']);
    exit;
}*/

// Bir session'un maksimum 30 dakika açık kalmasına izin ver (Güvenlik amaçlı ayar)
//ini_set('session.gc_maxlifetime', 30*60);

// Bir çerezin maksimum 30 dakika açık kalmasına izin ver (Yine, güvenlik için ayar)
//ini_set('session.cookie_lifetime', 30*60);
// PHP.INI İÇERİSİNE TAŞINDI.

header('X-Frame-Options: DENY');

date_default_timezone_set("Europe/Istanbul");

try{
    
    $pdo = new PDO("mysql:host=" . YUIN_DB_SERVER . ";dbname=" . YUIN_DB_NAME, YUIN_DB_USER, YUIN_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'; SET CHARSET 'utf8'");
    
}catch(PDOException $e) {
    
    echo '<h2 style="color:red;"><b>HATA!</b> Bir sorun meydana geldi! Lütfen herhangi bir yönetim kurulu üyesine bu hatayı aldığınızı bildirin.</h2>';
    if($debugLevel === 1) {
        
        print("HATA: Could not connect. " . $e->getMessage());
    }
    
    die();
}
?>