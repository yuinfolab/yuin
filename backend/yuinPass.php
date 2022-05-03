<?php

function yp_getUserIP() {
    
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }
    
    return $ip;
}

/* ====================================================== */
// BU MESAJIN ALTI OPEN SOURCE EDİL-MEYECEK!

// DİKKAT DİKKAT! Aynı fonksiyonlardan connect.php içerisinde de mevcut. Fonksiyonlar üzerinde değişiklik yapacaksanız orayıda değiştirmeyi unutmayın.

function generateYuinPass() {
    
    $ip = yp_getUserIP();
    if(isset($_SESSION['login']) && $_SESSION['login'] === 1) {
        
        $sip = $_SESSION['ip'];
    }else{
        
        $sip = $ip;
    }
    
    $iphash = sha1(md5($ip . '-' . $sip));
    $date = date("d-m-Y");
    
    $result = md5(sha1($ip . '-' . $sip . '-' . $iphash . '-' . $date . '-' . strrev($iphash) . sha1($date)));
    return $result;
}

function validateYuinPass() {
    
    $ip = yp_getUserIP();
    $ptr = gethostbyaddr($ip); // IP'nin DNS PTR kaydını al
    
    $yuinPass = generateYuinPass();
    if(isset($_COOKIE['yuinPass'])) {
        
        $value = $_COOKIE['yuinPass'];
        if($yuinPass === $value) {
            
            return true;
        }
    }
    
    if(strpos($ptr, 'google.com') !== false || strpos($ptr, 'googlebot.com') !== false || strpos($ptr, 'yandex.com') !== false || strpos($ptr, 'yandex.ru') !== false || strpos($ptr, 'yandex.net') !== false || strpos($ptr, 'search.msn.com') !== false || strpos($ptr, 'twttr.com') !== false || strpos($ptr, 'yahoo.net') !== false || strpos($ptr, 'duckduckgo.com') !== false) {
        
        // İyi bot
        return true;
    }
    
    return false;
}

function setYuinPass() {
    
    $yuinPass = generateYuinPass();
    setcookie('yuinPass', $yuinPass, time() + 86400);
    //$_COOKIE['yuinPass'] = $yuinPass;
}

// BU MESAJIN ÜSTÜ OPEN SOURCE EDİL-MEMELİ!
// Neden? Çünkü bu bizim bot korumamız. İleride biri bize musallat olursa onu bizden koruyacak tek şey bu.
/* ====================================================== */