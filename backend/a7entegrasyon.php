<?php

/*
*
*		Class A7_Entegrasyon
*		Geliştiren: Emrecan ÖKSÜM
*		Tarih: 13.12.2021
*		Açıklama:
*
*		Yeditepe Akademik7 Sistemi <-> yuin.yeditepe.edu.tr arası entegrasyon modülü
*		
*		yuin.yeditepe.edu.tr sitemize hızlı kaydolma için kullanılacak modülümüz.
*		Akademik7 paneline öğrenci bilgileri ile giriş yaptıktan sonra öğrencinin
*		kişisel bilgilerini çeker ve JSON formatında jQuery AJAX tarafından okunmak
*		üzere geri gönderir. Amaç, öğrencinin kişisel bilgilerinin güvenli bir şekilde
*		çekilmesi ve talep eden browser'a tekrar güvenli bir şekilde gönderilmesi.
*
*		KULLANIM ŞARTLARI:
*
*		A7 Entegrasyon modülü yazılırken veri güvenliği maksimum düzeyde tutulmuştur.
*		modülümüzü kendi sitelerinizde kullanabilirsiniz ancak modülümüz kullanılarak
*		çekilen bilgilerin saklanmasından modülün çalıştırıldığı sistemin yöneticisi
*		sorumludur.
*
*		Modülün kullanılacağı her durumda kullanıcıya modülün çekeceği kişisel bilgileri
*		bildirmek ve KVKK bilgilendirmesinde bulunmak, modülü kullanacak sistemin
*		yöneticisinin sorumluluğundadır.
*
*		Üstte belirtilen şartlara uyduğunuz sürece modülümüzü kendi sitelerinizde
*		Bilgi İşlem departmanının da izni olması koşulu ile kullanabilirsiniz.
*		
*		KULLANIM ŞEKLİ:
*		
*		$user = 'U2018XXXXXXX'; // Yeditepe öğrenci numaranız
*		$pass = 'akademik7Sifrem123'; // Yeditepe A7 öğrenci şifreniz
*		
*		$a7class = new A7_Entegrasyon($user, $pass);
*		$kisiselBilgiler = $a7class->tryLogin();
*		exit($kisiselBilgiler); // isim, soyisim, telno, eposta, fakulte, bolum gibi kisisel bilgileri JSON formatında getirir.
*		// Eger bir sorun oluşursa (boolean) false döndürür.
*		
*/

if(!defined("YUIN"))
    die("Güvenlik sebebiyle bu dosyaya direkt erişime izin verilmemektedir.");

class A7_Entegrasyon {
	
	const A7_LOGIN_API = 'https://api7.yeditepe.edu.tr/auth/a7login';
	const A7_NAMESURNAME_API = 'https://api7.yeditepe.edu.tr/user/identity/true/';
	const A7_PHONE_EMAIL_API = 'https://api7.yeditepe.edu.tr/user/cm/true/';
	const A7_STDTEMAIL_API = 'https://api7.yeditepe.edu.tr/student/email-info/true/';
	const A7_EDUCATION_API = 'https://api7.yeditepe.edu.tr/user/all-educations/true/';
	
	const WHERE_IS_MY_COOKIEJAR = '/home/yuinyeditepe/tmp/A7_LOGIN_API_COOKIES-';
	const VERBOSITY = false; /* Prodüksüyon kullanımında (boolean) false olması gereklidir. */
	
	private $user;
	private $pass;
	
	function __construct($user, $pass) {
		
		$this->user = $user;
		$this->pass = $pass;
	}
	
	function __destruct() {
		
		unset($this->user);
		unset($this->pass);
	}
	
	private function logger($inc) {
		
		if(!self::VERBOSITY) {
			
			return;
		}
		
		echo '[WARN] ' . $inc;
	}
	
	private function getCookieJar() {
		
		$user = $this->user;
		$cookiejar = self::WHERE_IS_MY_COOKIEJAR . md5(time() . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']) . '-' . $user;
		return $cookiejar;
	}
	
	private function breakCookieJar() {
		
		$cookiejar = $this->getCookieJar();
		if(file_exists($cookiejar)) {
			
			unlink($cookiejar);
		}
	}
	
	private function createCookieJar() {
		
		// Çok düşük bir ihtimal ancak veri güvenliğinden emin olmamız için handle edilmesi gereken bir ihtimal
		$this->breakCookieJar();
		$cookiejar = $this->getCookieJar();
		return touch($cookiejar);
	}
	
	private function httpRequest($url, $payload, $method, $timeout, $auth = false) {
		
		$cookiejar = $this->getCookieJar();
		if($method == 'post' && $method == 'json-post') {
			
			$post = true;
		}else{
			
			$post = false;
		}
		
		$this->logger('Preparing request...');
		
		$headers = [
			'Origin: https://a7.yeditepe.edu.tr',
			'Content-Type: application/json; charset=utf-8'
		];
		
		if($auth) {
			
			$headers[] = $auth;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// cURL nedense api7.yeditepe.edu.tr'nin SSL sertifikasını doğrulayamıyor.
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $post);
		/*curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar);*/
		curl_setopt($ch, CURLOPT_REFERER, 'https://a7.yeditepe.edu.tr/');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		if(!empty($payload) && $method == 'post') {
			$this->logger('Building postfields for HTTP query...');
			curl_setopt($ch, CURLOPT_POSTFIELDS, @http_build_query($payload));
		}else if(!empty($payload) && $method == 'json-post') {
			$this->logger('JSON encoding postfield for HTTP query...');
			$payload = json_encode($payload);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}
		
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		
		if(curl_errno($ch) == 0) {
			$this->logger('Sending query...');
			$ch_res = curl_exec($ch);
			curl_close($ch);
			return $ch_res;
			
		}else{
			$this->logger('curl error! ' . curl_error($ch));
			curl_close($ch);
			return false;
		}
	}
	
	public function tryLogin() {
		
		$this->logger('Starting process...');
		
		// Cookiejar aç - Akademik7 sistemi cookie girmiyor. Eğer girmeye başlarsa burayı uncomment ederek kolayca uyumlu hale getirebilirsiniz.
		//$this->createCookieJar();
		
		// A7 login başlangıç
		$loginPayload = [
			'user' => $this->user,
			'pass' => $this->pass,
			'rememberMe' => false
		];
		
		$a7call1 = $this->httpRequest(self::A7_LOGIN_API, $loginPayload, 'json-post', 30);
		
		if(!strpos($a7call1, 'ta7')) {
			
			$this->logger('Token not found in the initial response!');
			return false;
		}
		
		$a7token = @json_decode($a7call1, true);
		
		unset($loginPayload);
		unset($a7call1);
		
		if(!isset($a7token['ta7'])) {
			
			$this->logger('Token not found in the JSON decoded response!');
			return false;
		}
		
		$a7token = @$a7token['ta7'];
		
		$a7token_piece = @explode('.', $a7token);
		if(!isset($a7token_piece[0])) {
			
			$this->logger('JWT Token not found in the initial response!');
			return false;
		}
		
		$a7token_jwt = @base64_decode($a7token_piece[0]);
		$a7token_jwt = @json_decode($a7token_jwt, true);
		if(!isset($a7token_jwt['alg']) || $a7token_jwt['typ'] != 'JWT') {
			
			$this->logger('Invalid JWT Token!');
			return false;
		}
		
		$stdtinfo = @base64_decode($a7token_piece[1]);
		$stdtinfo = @json_decode($stdtinfo, true);
		
		if(!isset($stdtinfo['cci'])) {
			
			$this->logger('Student system ID not found in the JSON decoded response!');
			return false;
		}
		
		$stdtinfo = @$stdtinfo['cci'];
		$authHeader = 'Authorization: Bearer ' . $a7token;
		// A7 login bitiş
		
		// İsim Soyad çek
		$namesurname = $this->httpRequest(self::A7_NAMESURNAME_API . $stdtinfo, null, false, 30, $authHeader);
		if(!strpos($namesurname, 'firstName')) {
			
			$this->logger('Failed to retrieve name surname information!');
			return false;
		}
		
		$namesurname = @json_decode($namesurname, true);
		$namesurname = @$namesurname[0];
		
		$firstName = $namesurname['firstName'];
		$lastName = $namesurname['lastName'];
		unset($namesurname);
		// İsim Soyad çek bitiş
		
		// Telefon numarası Eposta adresi çek başlangıç
		$phoneemail = $this->httpRequest(self::A7_PHONE_EMAIL_API . $stdtinfo, null, false, 30, $authHeader);
		$phoneemailfound = true;
		
		if(!strpos($phoneemail, 'phone')) {
			$phoneemailfound = false;
			$phone = '05000000000';
		}
		
		if(!strpos($phoneemail, 'mail')) {
			$phoneemailfound = false;
			// Eğer öğrenci epostası kayıtlı değilse okul öğrenci eposta adresini çek
			$stdemail = $this->httpRequest(self::A7_STDTEMAIL_API . $stdtinfo, null, false, 30, $authHeader);
			if(!strpos($stdemail, 'KULLANICIADI')) {
				
				$this->logger('Failed to retrieve name student email information!');
				return false;
			}
			
			$stdemail = @json_decode($stdemail, true);
			$stdemail = @$stdemail[0];
			$stdemail = $stdemail['KULLANICIADI'] . '@std.yeditepe.edu.tr';
			if(!filter_var($stdemail, FILTER_VALIDATE_EMAIL)) {
				
				$this->logger('Failed to parse student email information!');
				return false;
			}
			
			$email = $stdemail;
			unset($stdemail);
		}
		
		if($phoneemailfound) {
			
			$phoneemail = @json_decode($phoneemail, true);
			$phoneemail = @$phoneemail[0];
			
			$phone = $phoneemail['phone'];
			$email = $phoneemail['mail'];
		}
		unset($phoneemail);
		// Telefon numarası Eposta adresi çek bitiş
		
		// Eğitim bilgileri çek başlangıç
		$egitimbilgi = $this->httpRequest(self::A7_EDUCATION_API . $stdtinfo, null, false, 30, $authHeader);
		
		if(!strpos($egitimbilgi, 'unit')) {
			
			$this->logger('Failed to retrieve education information!');
			return false;
		}
		
		$egitimbilgi = @json_decode($egitimbilgi, true);
		$egitimbilgi = $egitimbilgi[0];
		
		$fakulte = $egitimbilgi['unit'];
		$bolum = $egitimbilgi['department'];
		$ogrencino = $egitimbilgi['studentNo'];
		unset($egitimbilgi);
		// Eğitim bilgileri çek bitiş
		
		// Depolanan cookieleri yok et
		//$this->breakCookieJar();
		
		unset($stdtinfo);
		unset($a7token);
		unset($a7token_piece);
		unset($a7token_jwt);
		unset($authHeader);
		
		// Son evre başlangıç
		$bilgiler = [
			'isim' => $firstName,
			'soyisim' => $lastName,
			'telno' => $phone,
			'eposta' => $email,
			'fakulte' => $fakulte,
			'bolum' => $bolum,
			'ogrenciNo' => $ogrencino
		];
		
		$bilgiler = json_encode($bilgiler);
		return $bilgiler;
		// Son evre bitiş
	}
}