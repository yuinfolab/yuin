<?php

function didYouKnow() {
    
    $dyk = [
        'Bir hamam böceği, kafası olmadan iki hafta yaşayabilir çünkü beyinleri ayaklarındadır fakat başsız bir hamam böceği açlıktan ölecektir.',
        'İnsan gözü 526 megapikseldir. Eğer insan gözü kadar kaliteli bir fotoğraf makinesi olsaydı, çekilecek tek bir resmin boyutu 2.5 gb olurdu.',
        'Güneş\'ten salınan enerji o kadar fazladır ki, güneşin 1 saniyede yarattığı enerji 100 Milyar atom bombası enerjisinden büyüktür.',
        'Bir kadında 4.5 litre, bir erkekte ise ortalama 5.6 litre kan bulunuyor.',
        'Bir gün içerisinde yaşadığınız olayların yalnızca % 7′si kalıcı hafızada kalır.',
        'Bir köstebek sadece bir gecede 90 m. tünel kazabilir.',
        'Bir bardak sıcak su, buzdolabında soğuk sudan daha çabuk donar.',
        'Her insanın dilinin izi de parmak izi gibi farklıdır.',
        'Bal bozulmaz; o nedenle 3 bin yıllık bir balı bile yemiş olabilirsiniz!',
        'Henüz ölmüş insanların tüyleri sonradan diken diken olabilir.',
        '1903 yılında Wright kardeşler geliştirdikleri motorlu uçakla ilk kez uçtu. 66 yıl sonra ise yani 1969 yılında Ay\'a indik!',
        'Yazı dili Mısır, Sümer, Çin ve hatta Mayalardan bağımsız olarak bulundu.',
        '1 milyon 200 bin sivri sinek ayrı ayrı insanı soktuğunda ortalama bir insanın kanını tamamen tüketebilir.',
        'Vücudunuzun çok yağlı bir yemeği sindirmesi 6 saat sürerken, karbonhidrat ağırlıklı bir yemeği sindirmesi 2 saatte tamamlanır.',
        'Piramitlerin inşa edildiği günler mamutlar hala hayattaydı.',
        '1930 yılında keşfedilmesinden 2006 yılında gezegenlikten çıkarıldığı süre zarfında Plüton Güneş\'in yörüngesinde turunu henüz tamamlamamıştı. Plüton\'un yörüngesini tamamlaması 248 yılı buluyor.',
        'Dünya üzerindeki en büyük yaşayan organizma Armillaria dışarıdan bakıldığında ufak gibi görünse de toprak altında boyu 3 bin 800 metreyi buluyor.',
        'Samanyolu\'nda bulunan yıldızların sayısı dünya üzerinde bulunan ağaç sayısından azdır. Samanyolu\'nda bilinen 100 milyar yıldıza karşılık, dünyada 3 trilyonun üzerinde ağaç olduğu biliniyor.',
        'Orman yangınları yokuş yukarı daha hızlı yayılır.',
        'Çok fazla yerseniz, duyma kaliteniz düşer.',
        'Su ayılarını çıplak gözle görmemiz mümkün değil; 0.5 mm uzunluğunda olan bu yaratıklar en zorlu şartlarda bile hayatta kalabiliyor. Uzay boşluğunda bile yaşayabiliyor!',
        'Kürdan, Amerikalıların boğulmasına en fazla neden olan nesnedir.',
        'İtalyan bayrağının tasarımını Napoleon Bonaparte yapmıştır.',
        'Kağıt parçalar ilk kez Çin\'de kullanılmıştır.',
        'Ketçap önceleri ilaç olarak kullanılıyordu.',
        'Uzay yolculuğunda taşınacak her kilo için gerekli olan yakıt miktarı 530 kg\'dır.',
        'Salatalık bir sebze değil, meyvedir.',
        'Dracula, tarih boyunca sinemaya en fazla uyarlanan hikayedir.',
        'İnsanlar vücutlarında 300 adet kemikle doğuyorlar ama yetişkin olduklarında bu sayı 206\'ya düşüyor.',
        'Eskimolar buzdolaplarını yiyeceklerin donmaması için kullanırlar.',
        'Telefonun mucidi Alexander Graham Bell, karısı ve annesiyle hiçbir zaman telefonda konuşamadı. Çünkü ikisi de doğuştan sağırdı.',
        'İnsan teninin bir santimetrekaresi 625 tane ter bezi içerir.',
        'Çocuklar baharda daha fazla büyüyor.',
        'Koalalar, primatlar ve insanlar, kendilerine özgü parmak izi olan tek canlılardır.',
        'Pinokyo, İtalyanca\'da "Çam Göz" anlamına gelir.',
        'İngilizce\'de en fazla tanıma sahip olan kelime "Set" tir. Oxford İngilizce Sözlük\'te tam 464 tanım bulunmaktadır. Onu 396 tanımla "Run" ve 368 ile "Go" kelimeleri izler.',
        'İki gözünüz var ve her biri 130 milyon görme siniri hücresinden oluşuyor. Ve her bir hücrenin içinde 100 trilyon atom olduğu düşünüldüğünde bu sayı evrendeki tüm yıldızların sayısından bile daha fazla...',
        'Derin sularda 180 balık türü hiç ışık yüzü görmeden büyür ve yaşamını sürdürür.',
        'Dünya\'yı varoluşundan bu yana 24 saate sığdırmaya çalışsak insanlar bu sürenin sadece 1 dakika 17 saniyesini doldurabilir.',
        'Eğer bir kelimeyi hatırlamakta zorlanıyorsanız yumruğunuzu sıkın. Bu beyin aktivitenizin artmasına ve hafızanızı geliştirmeye yardımcı olur.',
        'Bölme ve çarpmanın eş değer olduğu tek bilim dalı biyolojidir.',
        'Acil durumlarda bir pastel boya 30 dakika boyunca yanabilir.',
        'Ağırlığı 600 kilogramı bulabilen bir mavi balinanın kalp damarlarının içine bir insan girebilir.',
        'Sadece tek bir gün dünyaya ulaşan güneş ışıkları ile dünya 27 yıl enerji ihtiyacını karşılayabilir.',
        'Her gün 275 milyon yeni yıldız doğuyor. Gerçekten inanılmaz!',
        'Bir mavi balinanın dili Afrika\'da yaşayan ortalama bir filin ebatlarıyla aynıdır.',
        'İnsan uyandığı andan itibaren beyin küçük bir ampulü yakacak kadar elektrik üretir.'
    ];
    
    $git = 'YUIN Club (yuin.yeditepe.edu.tr) websitesi ve yazılımı açık kaynak kodludur ve YUINFOLAB GitHub üzerinden geliştirilebilir. <a href="https://github.com/yuinfolab/yuin" target="_blank">Repo link</a>';
    
    $yazitura = rand(1,4);
    if($yazitura === 1) {
        
        return $git;
    }
    return $dyk[array_rand($dyk)];
}