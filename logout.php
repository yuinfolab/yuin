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

// YUIN Club'dan güvenli çıkış yap

session_start();

$_SESSION = [];
session_destroy();
if(isset($_GET['ipc'])) {
    
    header('Location: login.php?ipc');
    exit;
}
header('Location: index.php?logout');
exit;