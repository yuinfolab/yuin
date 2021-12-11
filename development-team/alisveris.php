<?php

session_start();

$baslangic = time();

define('YUIN',1);

// Veritabanınla bağlantı kur ve basit ayarları uygula (mesela debugging)
include "/home/yuinyeditepe/public_html/backend/connect.php";

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";

if($_SERVER['REQUEST_METHOD'] == 'POST' && is_numeric($_POST['ucret'])) {
    
    $alinacakMal = trim($_POST['alinacakMal']);
    $ucret = $_POST['ucret'];
    
    $stmt = $pdo->prepare('INSERT into alisverislistesi_test (alinacakMal,ucret) VALUES (:alinacakMal, :ucret)');
    $stmt->bindParam(':alinacakMal', $alinacakMal, PDO::PARAM_STR);
    $stmt->bindParam(':ucret', $ucret, PDO::PARAM_STR);
    $stmt->execute();
    unset($stmt);
    
    header('Location: alisveris.php?basarili');
    exit;
}

if(isset($_GET['sil']) && !empty($_GET['sil']) && is_numeric($_GET['sil'])) {
    
    $silinecek = $_GET['sil'];
    
    $stmt = $pdo->prepare('DELETE from alisverislistesi_test WHERE id = :id');
    $stmt->bindParam(':id', $silinecek, PDO::PARAM_STR);
    $stmt->execute();
    unset($stmt);
    
    header('Location: alisveris.php?basarili');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM alisverislistesi_test');
$stmt->execute();
$alisverisListesi = $stmt->fetchAll();

unset($stmt);
unset($pdo);
?>
<html>
    <head>
        <title>Alışveriş Listesi</title>
        <meta charset="utf-8" lang="tr">
        <link rel="stylesheet" href="../css/bootstrap.min.css">
    </head>
    <body>
        <?php
        
        if(isset($_GET['basarili'])) {
            
            ?>
            
            <div class="alert alert-success" role="alert">
Yaptığınız son işlem başarıyla tamamlandı.
            </div>
            
            <?php
        }
        
        ?>
        
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Alınacak şey</th>
                    <th scope="col">Ücret</th>
                    <th scope="col">Sil</th>
                </tr>
            </thead>
            <tbody>
                <?php
                
                foreach($alisverisListesi as $alinacak) {
                    
                    ?>
                    <tr>
                        <td><?=$alinacak['id'];?></td>
                        <td><?=$alinacak['alinacakMal'];?></td>
                        <td><?=$alinacak['ucret'];?></td>
                        <td><a href="?sil=<?=$alinacak['id'];?>" class="btn btn-dark">Sil</a>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <form method="post">
            <div class="form-group">
                <label for="alinacakMal">Alınacak Mal <b>(Makarna için boş bırakın)</b></label>
                <input type="text" name="alinacakMal" class="form-control" id="alinacakMal" placeholder="Domates" required>
            </div>
            <div class="form-group">
                <label for="alinacakMalUcret">Ücret <b>(Makarna için boş bırakın)</b></label>
                <input type="double" name="ucret" class="form-control" id="alinacakMalUcret" placeholder="12,99" required>
            </div>
            <center><button type="submit" class="btn btn-primary">Kaydet</button></center>
        </form>
        
    </body>
</html>