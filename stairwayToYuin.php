<?php

define('YUIN',1);

// Yardımcı fonksiyonları getir
require_once "/home/yuinyeditepe/public_html/backend/helpers.php";
require_once "/home/yuinyeditepe/public_html/backend/yuinPass.php";

$hedef = 'index.php';
if(isset($_GET['hedef']) && !empty(trim($_GET['hedef'])) && strpos(trim($_GET['hedef']), '.php') !== false) {
    
    $hedef = trim($_GET['hedef']);
    $hedef = preg_replace("/[^a-zA-Z.]/", "", $hedef);
    
    $ext = substr($hedef, -4);
    if($ext != '.php') {
        
        $hedef = 'index.php';
    }
}

if(validateYuinPass()) {
    
    header('Location: ' . $hedef);
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $error = '';
    
    if(!gRecaptchaVerify($_POST['g-recaptcha-response'])) {
        
        $error .= "Captcha doğrulaması başarısız oldu! lütfen tekrar dene." . PHP_EOL;
    }
    
    /*if(empty($error)) {
        
        setYuinPass();
        
        header('Location: ' . $hedef);
        exit;
    }else{
        
        die($error);
    }*/
    
    setYuinPass();
    header('Location: ' . $hedef);
}

?>
<html>
   <head>
      <title>Stairway to YUIN Club</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      
      <!-- Google Fonts -->
	  <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i" rel="stylesheet">
	  
	  <script src="https://kit.fontawesome.com/f65dc3fbad.js" crossorigin="anonymous"></script>
	  
	  <!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

      
      <style>
        
        p, h1, h2, h3, h4, h5, h6 {
            
            font-family: verdana;
        }
.v-wrap{
    height: 100%;
    white-space: nowrap;
    text-align: center;
}
.v-wrap:before{
    content: "";
    display: inline-block;
    vertical-align: middle;
    width: 0;
    /* adjust for white space between pseudo element and next sibling */
    margin-right: -.25em;
    /* stretch line height */
    height: 100%; 
}
.v-box{
    display: inline-block;
    vertical-align: middle;
    white-space: normal;
}
      </style>
   </head>
   <body>
      <script type="text/javascript">
      
      var yuinPassInit = function() {
          
          grecaptcha.render('captcha', {'callback':yuinPassLoad, 'sitekey':'6LfwnO8UAAAAANhxO1zsoDnlgAu8_KK0PnB4AqmW'});
      }
      
      var yuinPassLoad = function(resp) {
          
          document.getElementById("ypf").submit();
      }
      
      </script>
      <script src='https://www.google.com/recaptcha/api.js?onload=yuinPassInit&render=explicit' async defer></script>
      
      <div class="v-wrap">
    <article class="v-box">
         <img src="img/logo.png" alt="YUInformatics Logo" style="width: 350px; height: 100px;">
         <br><br>
         <h3><i style="color: red;" class="fas fa-robot"></i> <i style="color: red;" class="fas fa-times"></i> Sitemize yalnızca insanları alıyoruz. <i style="color: green;" class="fas fa-user-alt"></i> <i style="color: green;" class="fas fa-check-circle"></i></h3>
         <p><b>Lütfen kötü bir robot olmadığınızı doğrulamak için alttaki kutucuğa tıklayınız.</b></p>
         <p>Tarayıcınızda çerezlerin (cookies) açık olması gerekmektedir. Çoğunlukla çerezler zaten özel bir ayar yapılmadıkça açıktır.</p>
         <form method="post" id="ypf">
            <center><div id="captcha"></div></center>
         </form>
      </article></div>
   </body>
</html>