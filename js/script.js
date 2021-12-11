// A javascript-enhanced crossword puzzle [c] Jesse Weisbeck, MIT/GPL 
(function($) {
	$(function() {
		// provide crossword entries in an array of objects like the following example
		// Position refers to the numerical order of an entry. Each position can have 
		// two entries: an across entry and a down entry
		var puzzleData = [
			 	{
					clue: "Bir yazılımcının kodlamaya başladığında yazdırdığı ilk yazı",
					answer: "hello world",
					position: 1,
					orientation: "down",
					startx: 27,
					starty: 1
				},
			 	{
					clue: "C, C++, ve C# dillerinde öğrencilerin korkulu rüyası",
					answer: "pointer",
					position: 2,
					orientation: "down",
					startx: 18,
					starty: 10
				},
				{
					clue: "Bilgisayarları virüslere karşı koruyan yazılımlardır",
					answer: "antivirüs",
					position: 3,
					orientation: "down",
					startx: 23,
					starty: 8
				},
				{
					clue: "Alışverişin çevrimiçi ortamda gerçekleştirilmesine verilen ad",
					answer: "eticaret",
					position: 4,
					orientation: "down",
					startx: 15,
					starty: 2
				},
				{
					clue: "İnsanoğlunun gereklerine uygun yardımcı alet ve araçların yapılması ya da üretilmesi için gerekli bilgi ve yetenek olarak tanımlanan kavram",
					answer: "teknoloji",
					position: 5,
					orientation: "down",	
					startx: 20,
					starty: 8
				},
				{
					clue: "Bir bilginin toplanmasını, bu bilginin işlenmesini, bu bilginin saklanmasını ve gerektiğinde herhangi bir yere iletilmesi ya da herhangi bir yerden bu bilgiye erişilmesini otomatik olarak sağlayan teknolojiler bütününe verilen isim",
					answer: "bilişim teknolojileri",
					position: 6,
					orientation: "across",
					startx: 7,
					starty: 13
				},
				{
					clue: "Gönderici ve alıcı konumundaki iki insan ya da insan grubu arasında gerçekleşen duygu, düşünce, davranış ve bilgi alışverişinin ismi",
					answer: "iletişim",
					position: 7,
					orientation: "across",
					startx: 13,
					starty: 2
				},
				{
					clue: "Yenilikçilik\" veya \"Yenilik\" anlamlarına gelen kavram",
					answer: "inovasyon",
					position: 9,
					orientation: "across",
					startx: 15,
					starty: 4
				},
				{
					clue: "Bilginin toplanması, işlenmesi, değerlendirilmesi, dağıtımı ve kullanımı ile ilgili faaliyetlerin tümüne verilen ad",
					answer: "bilişim",
					position: 10,
					orientation: "down",
					startx: 8,
					starty: 8
				},
				{
					clue: "Bilgisayarın beyni olarak bilinen kısaca CPU diye ifade edilen donanım birimi",
					answer: "işlemci",
					position: 11,
					orientation: "down",
					startx: 12,
					starty: 7
				},
				{
					clue: "Eğitim kurumlarında kullanılan site uzantısı",
					answer: "edu",
					position: 12,
					orientation: "down",
					startx: 25,
					starty: 13
				},
				{
					clue: "Üzerinde tuşlar olan ve yazı yazmaya yarayan donanım birimi",
					answer: "klavye",
					position: 13,
					orientation: "across",
					startx: 13,
					starty: 15
				},
				{
					clue: "Bilgisayarda yer alan görüntüleri görmemizi sağlayan donanım birimi",
					answer: "monitör",
					position: 14,
					orientation: "across",
					startx: 9,
					starty: 7
				},
				{
					clue: "Elektrikler kesilince içerisindeki bilgilerin silindiği donanım birimi",
					answer: "ram",
					position: 15,
					orientation: "down",
					startx: 4,
					starty: 4
				},
				{
					clue: "Bilgisayarın elle tutulan gözle görülen parçaları",
					answer: "donanım",
					position: 16,
					orientation: "down",
					startx: 9,
					starty: 1
				},
				{
					clue: "Kasa içerisinde en büyük yer kaplayan donanım birimidir",
					answer: "anakart",
					position: 17,
					orientation: "across",
					startx: 22,
					starty: 9
				},
				{
					clue: "Bilgisayarda yer alan resim yazı ve grafikleri kağıda aktaran donanım birimi",
					answer: "yazıcı",
					position: 18,
					orientation: "down",
					startx: 7,
					starty: 3
				},
				{
					clue: "Bizler için faydalı gibi görünen ama bilgisayara zarar veren yazılımlardır",
					answer: "truva atı",
					position: 19,
					orientation: "across",
					startx: 3,
					starty: 4
				}
				,/*
				{
					clue: "\"Skype, Tango, E-Mail\" diye örnekler veren öğretmen BİT’in hangi kullanım alanlarından bahsetmektedir",
					answer: "iletişim",
					position: 20,
					orientation: "down",
					startx: 7,
					starty: 9
				}*/
			] 
	
		$('#puzzle-wrapper').crossword(puzzleData);
		
	})
	
})(jQuery)
