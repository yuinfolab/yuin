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

define('YUIN',1);

// Veritabanınla bağlantı kur ve basit ayarları uygula (mesela debugging)
include "/home/yuinyeditepe/public_html/backend/connect.php";

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";

// Etkinlik ID
$eid = false;
if(isset($_GET['eid']) && !empty($_GET['eid']) && is_numeric($_GET['eid'])) {
    
    $eid = trim($_GET['eid']);
}

// Placeholder. Eğer fotoğraf yüklenemezse gösterilecek olan fotoğraf
$ph = '../img/yeditepe-icon.png';

if(!$eid) {
    
    // Tarayıcının bu scripti fotoğraf olarak algılaması için Content-Type başlığını image/png olarak gönder
    header('Content-Type: image/png');
    readfile($ph);
    exit;
}

// Talep edilen etkinliğe ait afişi çek
$stmt = $pdo->prepare("SELECT banner FROM etkinlik WHERE id = :id");
$stmt->bindParam(':id', $eid, PDO::PARAM_INT);
$stmt->execute();
$etkinlik = $stmt->fetch();

unset($stmt);
unset($pdo);

$etkinlik = @$etkinlik['banner'];
$etkinlik = str_replace('data:',null,$etkinlik);
$etkinlik = str_replace(';base64',null,$etkinlik);

$etkinlikP = explode(',', $etkinlik);

$imgProp = $etkinlikP[0];
$etkinlik = $etkinlikP[1];

$etkinlik = base64_decode($etkinlik);
if(empty($etkinlik)) {
    
    // Tarayıcının bu scripti fotoğraf olarak algılaması için Content-Type başlığını image/png olarak gönder
    header('Content-Type: image/png');
    readfile($ph);
    exit;
}

// Tarayıcının bu scripti fotoğraf olarak algılaması için Content-Type başlığını image/png olarak gönder
header('Content-Type: ' . $imgProp);
sendCacheHdrs('+1 month');
echo $etkinlik;