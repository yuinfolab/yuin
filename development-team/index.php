<?php

function faktoryel($fak) {
    
    $sonuc = 1;
    for($i = $fak; $i >= 1; $i--) {
        $sonuc *= $i;
    }
    
    return $sonuc;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && is_numeric(trim($_POST['faktoryel']))) {
    
    $fak = trim($_POST['faktoryel']);
    echo '<p>Hesaplama tamamlandı: ' . faktoryel($fak) . '</p>';
    echo '<a href="index.php">Tekrar hesapla</a>';
    exit;
}

?>

<html>
    <head>
        <title>Faktöryel Hesaplama</title>
    </head>
    <body>
        <h2>Faktöryel Hesaplama Sistemi</h2>
        <p>Lütfen faktöryel hesaplamak için bir sayı giriniz</p>
        <form method="post">
            <input type="text" name="faktoryel" value="">
            <input type="submit">
        </form>
    </body>
</html>