<?php
//
// +---------------------------------------------------------------------------+
// | eburhan VT Class v1.5                                                     |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | Sınıf adı      : eburhan VT Class                                         |
// | Versiyonu      : 1.5                                                      |
// | Görevi         : mySQL veritabanı yönetimini kolaylaştırmak               |
// | Gereksinimler  : mysql(i) eklentisi, php 5 ve yukarısı                    |
// | Son güncelleme : 23 Ocak 2010                                             |
// |                                                                           |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | Programcı      : Erhan BURHAN                                             |
// | E-posta        : eburhan {at} gmail {dot} com                             |
// | Web adresi     : http://www.eburhan.com/                                  |
// |                                                                           |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | Copyright (C)                                                             |
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// +---------------------------------------------------------------------------+
//



class VT
{
// veritabanı değişkenleri
protected $vt_name;
protected $vt_user;
protected $vt_pass;
protected $vt_host;
protected $vt_link;

// sorgu değişkenleri
protected $sorguSonucu;
protected $sorguSayisi;
protected $sorguKaynak;
protected $sorguSuresi;
protected $sorguTarihi;

// işlem değişkenleri
protected $insertID;
protected $numRows;
protected $affRows;

// cache değişkenleri
protected $cacheDurum;
protected $cacheLimit;
protected $cacheZaman;
protected $cacheDosya;

// hata ve çıktı değişkenleri
protected $hata;
protected $hataGercek;
protected $hataGoster;
protected $hataKaydet;
protected $hataDurdur;

// diğer değişkenler
protected $realEscape;
protected $debugBack;
protected $kayitYolu;
protected $almaModu;
protected $sql;


/**
* kurucu metotdur. sınıf gereksinimlerini kontrol eder ve varsayılan ayarları atar
* ### mümkün olduğunca bu metot içerisinde herhangi bir değişiklik yapmayınız ###
*
* @access public
*/
public function __construct()
{
if( version_compare(PHP_VERSION, '5.0.0') === -1 )
die('<strong>VT Error:</strong> bu sınıfının kullanılabilmesi için enaz PHP 5 sürümü gereklidir.');

if( ! extension_loaded('mysqli') )
die('<strong>VT Error:</strong> bu sınıfının kullanılabilmesi için "mysql(i)" eklentisi gereklidir.');

// bağlantı değişkenleri
$this->vt_name      = 'test';         // bağlanılacak olan veritabanı adı
$this->vt_user      = 'root';         // veritabanı için kullanıcı adı
$this->vt_pass      = '';             // veritabanı için parola
$this->vt_host      = 'localhost';    // bağlanılacak olan veritabanının adresi
$this->vt_lang      = 'latin5';       // dil (lang) durumu
$this->vt_link      = null;           // bağlantı (link) durumu

// sorgu değişkenleri
$this->sorguSonucu  = null;           // sorgu sonucu
$this->sorguSayisi  = 0;              // toplam sorgu sayısı
$this->sorguKaynak  = null;           // sorgu sonuçları veritabanından mı yoksa cache'den mi?
$this->sorguSuresi  = 0;              // sorgu ne kadar zaman aldı?
$this->sorguTarihi  = null;           // sorgu hangi tarihte yapıldı?

// işlem değişkenleri
$this->insertID     = 0;             // ekledikten sonra oluşan ID numarası
$this->numRows      = 0;             // toplam satır sayısı
$this->affRows      = 0;             // işlemden etkilenen satır sayısı

// cache değişkenleri
$this->cacheDurum   = false;         // son cache durumunu tutar. önbellekleme yapılacak mı yapılmayacak mı?
$this->cacheLimit   = 0;             // numRows sayısı bu değere ulaşmadığı sürece cache yapma
$this->cacheZaman   = 0;             // saniye cinsinden maksimum cache süresi
$this->cacheDosya   = null;          // cache dosyasının tam yolunu tutan değişken

// hata ve çıktı değişkenleri
$this->hata         = array();       // hataları tutan dizi
$this->hataGercek   = false;         // veritabanının ürettiği gerçek hatalar mı gösterilsin?
$this->hataGoster   = true;          // hatalar ekranda gösterilsin mi?
$this->hataKaydet   = true;          // hatalar bir dosyaya kaydedilsin mi?
$this->hataDurdur   = true;          // hata oluştuğunda programdan çıkılsın mı?

// diğer değişkenler
$this->realEscape   = function_exists('mysqli_real_escape_string');
$this->debugBack    = function_exists('debug_backtrace');
$this->kayitYolu    = dirname(__FILE__).DIRECTORY_SEPARATOR.'kayitlar'.DIRECTORY_SEPARATOR;
$this->almaModu     = 'obj'; // veri alma modu
$this->sql          = null;
}



//---------------------------------------------------------------------------
//    Seçenek Belirleme
//---------------------------------------------------------------------------
/**
* veritabanı tarafından üretilen gerçek hata mesajlarının göstirilip gösterilmeyeceğini belirler
*
* @access public
* @since 1.3
* @param bool gerçek hatalar gösterilsin mi? (true | false)
*/
public function hataGercek($val=true)
{
if( is_bool($val) )
$this->hataGercek = $val;

return $this;
}


/**
* bir hata oluştuktan sonra, programdan çıkılıp çıkılmayacağını belirler
*
* @access public
* @param bool programdan çıkılsın mı? (true | false)
*/
public function hataDurdur($val=true)
{
if( is_bool($val) )
$this->hataDurdur = $val;

return $this;
}


/**
* oluşan hataların ekranda gösterilip gösterilmeyeceğini belirler
*
* @access public
* @param bool hatalar gösterilsin mi? (true | false)
*/
public function hataGoster($val=true)
{
if( is_bool($val) )
$this->hataGoster = $val;

return $this;
}


/**
* oluşan hataların bir dosyaya kaydedilip kaydedilmeyeceğini belirler
*
* @access public
* @param bool hatalar kaydedilsin mi? (true | false)
*/
public function hataKaydet($val=true)
{
if( is_bool($val) )
$this->hataKaydet = $val;

return $this;
}


/**
* verilerin kaydedileceği klasör yolu
*
* @access public
* @param string klasör yolu
* @return boolean
*/
public function kayitYolu($yol)
{
// klasör yolunun sonunda \ veya / var mı?
if( substr($yol, -1) != DIRECTORY_SEPARATOR )    $yol = $yol.DIRECTORY_SEPARATOR;
// klasör yolundaki dizin ayraçlarını değiştir
$yol = preg_replace('%(?:\\\\|/)+%i', DIRECTORY_SEPARATOR, $yol);

// 1) klasör yoksa
if( ! file_exists($yol) && ! mkdir($yol, 0777) ) {
$this->_hataOlustur(__LINE__, __FUNCTION__, -1);
$this->_hataKontrol();
return false;
}

// 2) klasör varsa ama yazılabilir değilse
if( ! is_writable($yol) && ! chmod($yol, 0777) ) {
$this->_hataOlustur(__LINE__, __FUNCTION__, -2);
$this->_hataKontrol();
return false;
}

// herşey tamamsa 'kayitYolu' değişkenini güncelle
$this->kayitYolu = $yol;
return $this;
}


/**
* varsayılan veri alma modunu değiştirir
*
* @access public
* @param string alma modu (obj, arr, num)
*/
public function almaModu($mod)
{
if( in_array($mod, array('obj', 'arr', 'num')) )
$this->almaModu = $mod;

return $this;
}


/**
* cache'e yazılacak veri enaz kaç satır olmalı?
*
* @access public
* @param integer minimum satır sayısı
*/
public function cacheLimit($min)
{
if( is_int($min) && $min > 0 )
$this->cacheLimit = $min;

return $this;
}


/**
* cache'e yazılacak sorgular için varsayılan cache zamanı
*
* @access public
* @param integer cache zamanı (dakika)
*/
public function cacheZaman($dk)
{
if( is_int($dk) && $dk > 0 )
$this->cacheZaman = $dk * 60; // saniyeye dönüştü

return $this;
}



//---------------------------------------------------------------------------
//    Bağlantı açma & kapama
//---------------------------------------------------------------------------
/**
* veritabanı bağlantısı açar
*
* @access public
* @param string veritabanı ismi
* @param string veritabanı kullanıcı adı
* @param string veritabanı parolası
* @param string veritabanı adresi
* @return bool
*/
public function baglan()
{
$args = func_get_args();

// bağlantı ayarları bir ARRAY içindeyse
if( is_array($args[0]) ) {
$this->vt_name = is_string($args[0]['name']) ? $args[0]['name'] : 'test';
$this->vt_user = is_string($args[0]['user']) ? $args[0]['user'] : 'root';
$this->vt_pass = is_string($args[0]['pass']) ? $args[0]['pass'] : '';
$this->vt_host = is_string($args[0]['host']) ? $args[0]['host'] : 'localhost';
$this->vt_lang = is_string($args[0]['lang']) ? $args[0]['lang'] : 'latin5';
} else {
$this->vt_name = is_string($args[0]) ? $args[0] : 'test';
$this->vt_user = is_string($args[1]) ? $args[1] : 'root';
$this->vt_pass = is_string($args[2]) ? $args[2] : '';
$this->vt_host = is_string($args[3]) ? $args[3] : 'localhost';
$this->vt_lang = is_string($args[4]) ? $args[4] : 'latin5';
}

// bağlantı tutucuyu boşalt
$this->vt_link = null;

// veritabanına bağlan
if( ! $this->_veritabaniBaglan() )  return false;
// if( ! $this->_veritabaniSec() ) return false;

return true;
}


/**
* farklı bir veritabanı seçer
*
* @access public
* @param string veritabanı adı
* @return bool
*/
public function sec($vt)
{
$this->vt_name = $vt;
$this->_veritabaniSec();

return $this;
}


/**
* veritabanı bağlantısını kapatır
*
* @access public
* @return boolean
*/
public function __destruct()
{
// bağlantıyı kapat
if( $this->vt_link ) {
mysqli_close($this->vt_link);
$this->vt_link = null;
return;
}
}




//---------------------------------------------------------------------------
//    Sorgu oluşturma
//---------------------------------------------------------------------------
/**
* SQL cümleciğini atar
*
* @access public
* @param string SQL cümleciği
*/
public function sql($sql)
{
// girilen sql cümlesindeki gereksiz boşlukları ve sekmeleri temizle
$this->sql = preg_replace('/\s\s+|\t\t+/', ' ', trim($sql));

return $this;
}


/**
* SQL cümleciğindeki argümanları alır ve temizler
*
* @access public
*/
public function arg()
{
// argümanları al
$args = func_get_args();

// argümanların herbirini temizlenmeye gönder :)
$args = array_map(array($this, '_temizle'), $args);

// temizlenmiş argümanları %s ile değiştir
$this->sql = vsprintf($this->sql, $args);

return $this;
}


/**
* SQL cümleciğine bakarak verileri cache dosyasından veya veritabanından okur
*
* @access public
* @param integer cache zaman
*  @param integer cache limit
* @return boolean
*/
public function sor($cacheZaman=null, $cacheLimit=null)
{
// buraya kadar bir hata oluştuysa çık
if( count($this->hata) > 0 ) return false;

// önceki cache değerlerini tutan değişkenler
$cacheZamanOnceki = $cacheLimitOnceki = null;

// Bu sorguda geçerli olacak Cache Zaman değeri
if( ! is_null($cacheZaman) && (int) $cacheZaman >= 0 ) {
$cacheZamanOnceki = $this->cacheZaman;
$this->cacheZaman = $cacheZaman * 60; // 60 ile çarpınca saniyeye dönüştü
}

// Bu sorguda geçerli olacak Cache Limit değeri
if( ! is_null($cacheLimit) && (int) $cacheLimit >= 0 ) {
$cacheLimitOnceki = $this->cacheZaman;
$this->cacheLimit = $cacheLimit;
}

// şartlar cache'den okuma yapmaya müsait mi?
if( $this->_cacheMusait() ){
// cache'den oku
$this->sorguSonucu = $this->_cacheOku();
$this->sorguKaynak = 'cache';
$this->numRows = count($this->sorguSonucu);
} else {
// herşey tamamsa veritabanından okuma yap
$this->sorguSonucu = $this->_veritabaniOku();
$this->sorguKaynak = 'veritabanı';

// sorgu sonucu FALSE değilse ve cache kaydı isteniyorsa...
if( $this->sorguSonucu && $this->cacheZaman > 0 && ($this->numRows >= $cacheLimit) ) {
$this->_cacheYaz();
}
}

// daha önceki zamanı ve limit geri yükle
if( ! is_null($cacheZamanOnceki) ) { $this->cacheZaman = $cacheZamanOnceki; }
if( ! is_null($cacheLimitOnceki) ) { $this->cacheLimit = $cacheLimitOnceki; }

// sorgu sonucu ya FALSE olur ya da veritabanından dönen değer
return ($this->sorguSonucu===false) ? false : true;
}



//---------------------------------------------------------------------------
//    Sorgu sonucunu alma
//---------------------------------------------------------------------------
/**
* Sorgu sonucunda elde edilen bütün verileri alır
*
* @access public
* @param string veri alma modu (obj, arr, num)
* @return mixed
*/
public function alHepsi($mod=null)
{
// sorgu sonucunda geriye bir değer dönmemişse (boşsa)
if( empty($this->sorguSonucu) ) return array();

// dışarıdan gelen mod geçerli değilse, varsayılanı kullan
if( in_array($mod, array('obj', 'arr', 'num'))===false ) {
$mod = $this->almaModu;
}

// nesne
if( $mod==='obj' )    return $this->sorguSonucu;
// dizi
if( $mod==='arr' )    return array_map('get_object_vars', $this->sorguSonucu);
// numaralandırılmış dizi
if( $mod==='num' ) {
$temp = array_map('get_object_vars', $this->sorguSonucu);
$temp = array_map('array_values', $temp);
return $temp;
}
}


/**
* Tek bir satırdaki bütün verileri alır
*
* @access public
* @param integer birden fazla satır geriye döndüyse kaçıncı satırın yakalancak?
* @param string veri alma modu (obj, arr, num)
* @return mixed
*/
public function alSatir($sno=1, $mod=null)
{
// sorgu sonucunda geriye bir değer dönmemişse (boşsa)
if( empty($this->sorguSonucu) ) return array();

// diziler 0'dan başladığı için 1 eksilt. böylece,
// kullanıcı 1 girdiğinde dizinin 0. elemanı gelecek
$sno -= 1;

// dışarıdan gelen mod geçerli değilse, varsayılanı kullan
if( in_array($mod, array('obj', 'arr', 'num'))===false ) {
$mod = $this->almaModu;
}

// satır numarası, dizi limitleri dışına çıkmamalı
if( ! is_int($sno) || $sno < 0 ) return array();
if( $sno >= $this->numRows ) return array();

// numaralandırılmış dizi
if( $mod==='num' )    return array_values(get_object_vars($this->sorguSonucu[$sno]));
// dizi
if( $mod==='arr' )    return get_object_vars($this->sorguSonucu[$sno]);
// nesne
if( $mod==='obj' )    return $this->sorguSonucu[$sno];
}


/**
* Yalnızca bir tek veri alır. alınacak veri yoksa NULL geri döndürür
*
* @access public
* @return mixed
*/
public function alTek()
{
// sorgu sonucunda geriye bir değer dönmemişse (boşsa)
if( empty($this->sorguSonucu) ) return null;

$dizi = array_values(get_object_vars($this->sorguSonucu[0]));
return $dizi[0];
}


/**
* SQL cümleciğinin en son halini verir
*
* @access public
* @return string SQL cümleciğinin son hali
*/
public function alSql()
{
return $this->sql;
}



//---------------------------------------------------------------------------
//    İşlem sonucunu alma
//---------------------------------------------------------------------------
/**
* Son sorgudan, tablodaki kaç satırın etkilendiğini verir
*
* @access public
* @return integer
*/
public function affRows()
{
return $this->affRows;
}


/**
* Son sorgudan sonra elde edilen satır sayısı
*
* @access public
* @return integer
*/
public function numRows()
{
return $this->numRows;
}


/**
* En son eklenen verinin ID'si
*
* @access public
* @return integer
*/
public function insertID()
{
return $this->insertID;
}


/**
* Toplam sorgu sayısını verir
*
* @access public
* @return integer
*/
public function sorguSayisi()
{
return $this->sorguSayisi;
}


/**
* Son sorgu için harcanan süre
*
* @access public
* @return integer
*/
public function sorguSuresi()
{
return $this->sorguSuresi;
}



//---------------------------------------------------------------------------
//    Hata işleme & Bilgi alma
//---------------------------------------------------------------------------
/**
* sınıf içerisinde kullanılan değişkenlerin bilgilerini verir
*
* @access public
* @return array
*/
public function bilgiVer()
{
return array (
'veritabani'    => $this->vt_name,
'kullanici'     => $this->vt_user,
'sunucu'        => $this->vt_host,
'link'          => $this->vt_link,
'sonSQL'        => $this->sql,
'ilkSonuc'      => isset($this->sorguSonucu[0]) ? $this->sorguSonucu[0] : array(),
'numRows'       => $this->numRows,
'affRows'       => $this->affRows,
'insertID'      => $this->insertID,
'sorguSayisi'   => $this->sorguSayisi,
'sorguKaynak'   => $this->sorguKaynak,
'sorguSuresi'   => $this->sorguSuresi,
'sorguTarihi'   => $this->sorguTarihi,
'almaModu'      => $this->almaModu,
'cacheLimit'    => $this->cacheLimit,
'cacheZaman'    => $this->cacheZaman,
'cacheDosya'    => $this->cacheDosya,
'ilkHataTR'     => count($this->hata) > 0 ? $this->hata[0]['user'] : null,
'ilkHataEN'     => count($this->hata) > 0 ? $this->hata[0]['real'] : null,
'kayitYolu'     => $this->kayitYolu
);
}


/**
* sınıf içerisinde kullanılan değişkenlerin bilgilerini ekrana yazdırır
*
* @access public
* @param boolean programdan çıkılsın mı?
*/
public function bilgiBas($exit=true)
{
$this->dump($this->bilgiVer());
if( $exit ) exit();
}


/**
* Herhangi bir işlem sonucunu, formatlı bir şekilde ekrana yazdırır
*
* @access public
* @param mixed yazdırılacak veri
*/
public function dump($veri)
{
print '<pre>';
print_r( $veri );
print '</pre>';
}



//---------------------------------------------------------------------------
//    Yardımcı fonksiyonlar
//---------------------------------------------------------------------------
/**
* hata çıktısını oluşturur
*
* @access protected
* @param string line
* @param string func
* @param string errNo
*/
protected function _hataOlustur($line, $func, $errNo)
{
// debug_backtrace() fonksiyonu varsa
if( $this->debugBack ){
// hatayı oluştur
$hataIlk = debug_backtrace();
$hataSon = array();

foreach( $hataIlk AS $hata ){
// 'class' anahtarı yoksa diğer hataya geç
if( ! isset($hata['class']) ) continue;

// oluşan hatanın sebebi bu class mı?
if( $hata['class'] === __CLASS__ ) {
array_push($hataSon, $hata);
}
}

// hatanın en son oluştuğu yerle ilgili bilgiler
$hataSon = end($hataSon);
} else {
$hataSon = array();
$hataSon['file'] = $this->_phpSelf();
$hataSon['line'] = $line;
$hataSon['function'] = $func;
}

array_push($this->hata, array(
'file' => $hataSon['file'],
'line' => $hataSon['line'],
'func' => __CLASS__.'::'.$hataSon['function'],
'user' => $this->_errUser($errNo),
'real' => $this->_errMsgDb()===false ? $this->_errUser($errNo) : $this->_errMsgDb(),
'sqlc' => $this->sql
));
}


/**
* oluşan hataların kaydedilmesi ve gösterilmesi işlemlerini kontrol eder
*
* @access protected
*/
protected function _hataKontrol()
{
// hataları dosyaya kaydet
if( $this->hataKaydet && count($this->hata)>0 ) {
$veri = "vt   : $this->vt_name".PHP_EOL.
"sql  : ".$this->hata[0]['sqlc'].PHP_EOL.
"hata : ".$this->hata[0]['real'].PHP_EOL.
"fonk : ".$this->hata[0]['func'].PHP_EOL.
"satir: ".$this->hata[0]['line'].PHP_EOL.
"dosya: ".$this->hata[0]['file'].PHP_EOL.
"zaman: ".date('d.m.Y H:i:s').PHP_EOL.PHP_EOL;

$this->_dosyayaKaydet($this->kayitYolu.date('d-m-Y').'.error', $veri);
}

// hataları ekranda göster
if( $this->hataGoster && count($this->hata)>0 ) {
printf(
'<pre class="vt_hata">'.PHP_EOL.
'<strong>VT HATA</strong>'.PHP_EOL.
'dosya : %s'.PHP_EOL.
'satir : %u'.PHP_EOL.
'mesaj : %s'.PHP_EOL.
'</pre>%s',
$this->hata[0]['file'],
$this->hata[0]['line'],
$this->hataGercek===false ? $this->hata[0]['user'] : $this->hata[0]['real'],
PHP_EOL
);

if( $this->hataDurdur ) exit();
}
}


/**
* veritabanına bağlanır
*
* @access protected
* @return boolean
*/
protected function _veritabaniBaglan()
{
// yeni bir bağlantı aç
$this->vt_link = mysqli_connect($this->vt_host, $this->vt_user, $this->vt_pass, $this->vt_name);
if( ! $this->vt_link ){
$this->_hataOlustur(__LINE__, __FUNCTION__, $this->_errNoDb());
$this->_hataKontrol();
return false;
}
mysqli_set_charset($this->vt_link, $this->vt_lang);
return true;
}


/**
* veritabanı seçer
*
* @access protected
* @return boolean
*/
protected function _veritabaniSec()
{
if( ! mysqli_select_db($this->vt_name, $this->vt_link) ){
$this->_hataOlustur(__LINE__, __FUNCTION__, $this->_errNoDb());
$this->_hataKontrol();
return false;
}

return true;
}


/**
* veritabanına sorgu gönderip sonuçlarını değerlendirir
*
* @access protected
* @return mixed
*/
protected function _veritabaniOku()
{
// veritabanı bağlantısı başlatılmamışsa...
if( ! $this->vt_link ){
$this->_hataOlustur(__LINE__, __FUNCTION__, -3);
$this->_hataKontrol();
return false;
}

// sorguyu gerçekleştir
$basla = $this->_timer();
$sorgu = mysqli_query($this->vt_link, $this->sql);
$bitir = $this->_timer();

// sorgu istatistikleri
$this->sorguSuresi = ($bitir-$basla);
$this->sorguTarihi = date('d.m.Y H:i:s');
$this->sorguSayisi++;

// bir önceki sorgunun bazı bilgilerini resetle
$this->numRows = $this->insertID = $this->affRows = 0;

// 1. sorgu başarısız ise
if( $sorgu === false ) {
$this->_hataOlustur(__LINE__, __FUNCTION__, $this->_errNoDb());
$this->_hataKontrol();
return false;
}

// 2. sorgu başarılı ama geriye bir sonuç döndürmüyorsa
// INSERT, UPDATE, DELETE veya REPLACE türündeki sorgular
if( $sorgu === true ) {
$this->insertID = mysqli_insert_id($this->vt_link);
$this->affRows  = mysqli_affected_rows($this->vt_link);
return true;
}

// 3. sorgu başarılı ve geriye bir sonuç döndürdüyse
// SELECT veya SHOW türündeki sorgular
$sonuc = array();
while( $satir = mysqli_fetch_object($sorgu) ) {
$sonuc[] = $satir;
}
mysqli_free_result($sorgu);
$this->numRows = count($sonuc);
return $sonuc;
}


/**
* cache'den okuma yapılmaya müsait mi?
*
* @access protected
* @return boolean
*/
protected function _cacheMusait()
{
// cache zamanı 0 ise cache özelliği kapalı demektir
if( $this->cacheZaman === 0 ) return false;

// eğer SELECT ve SHOW dışında bir sorgu yapıldıysa cache yapılamaz!
if( ! preg_match('/^(select|show)\s/i', $this->sql) ) return false;

// cache dosyasının yolu
$this->cacheDosya = $this->kayitYolu.md5($this->vt_name.$this->sql).'.cache';

// cache dosyası yoksa geri dön
if( ! file_exists($this->cacheDosya) ) return false;

// cache zamanı geçmişse geri dön (önce cache dosyasını sil)
if( time() - filemtime($this->cacheDosya) > $this->cacheZaman ) {
unlink($this->cacheDosya);
return false;
}

// herşey tamamsa TRUE geri döndür
return true;
}


/**
* cache dosyasından okuma yapar
*
* @access protected
* @return string
*/
protected function _cacheOku()
{
$basla = $this->_timer();
$verim = unserialize(file_get_contents($this->cacheDosya));
$bitir = $this->_timer();

// sorgu istatistikleri
$this->sorguSuresi = ($bitir - $basla);
$this->sorguTarihi = date('d.m.Y H:i:s');

return $verim;
}


/**
* cache dosyasına veri yazar
*
* @access protected
*/
protected function _cacheYaz()
{
// 'numRows' değeri ancak 'cacheLimit' değerinden büyükse cache yap
if( $this->cacheLimit === 0 || ($this->cacheLimit <= $this->numRows) )
$this->_dosyayaKaydet($this->cacheDosya, serialize($this->sorguSonucu));
}


/**
* Zararlı olabilecek verileri temizler
*
* @access protected
* @param string temizlenecek veri
* @return mixed
*/
protected function _temizle($veri)
{
if( is_null($veri) ) return 'NULL';
if( is_numeric($veri) ) return $veri;

if( get_magic_quotes_gpc() ) {
$veri = stripslashes($veri);
}

if( $this->realEscape ) {
$veri = mysqli_real_escape_string($this->vt_link, $veri);
} else {
$veri = addslashes($veri);
}

return "'$veri'";
}


/**
* Veritabanının kendi ürettiği hata numarası
*
* @access protected
* @return integer
*/
protected function _errNoDb()
{
if( $this->vt_link )
return mysqli_errno($this->vt_link);
}


/**
* Veritabanının kendi ürettiği hata mesajı
*
* @access protected
* @return string
*/
protected function _errMsgDb()
{
if( $this->vt_link )
return mysqli_error($this->vt_link);
}


/**
* hata numarasına karşılık gelen hata mesajını verir
*
* @param integer hata numarası
* @access protected
* @return string
*/
protected function _errUser($errNo)
{
// eksi (-) hatalar kullanıcı hataları
// artı (+) hatalar veritabanı hataları
$hata = array(
-1   => 'Kayıt klasörü bulunamıyor',
-2   => 'Kayıt klasörü yazılabilir değil',
-3   => 'Veritabanı bağlantısı başlatılmamış görünüyor',

// Server Error Codes and Messages
1044 => 'Erişim engellendi. Veritabanı ismini kontrol edin',
1045 => 'Erişim engellendi. Kullanıcı adını veya şifreyi kontrol edin',
1046 => 'Veritabanı seçilemedi. Veritabanı ismini kontrol edin',
1048 => 'İlgili kolona (sütuna) boş veri giremezsiniz',
1049 => 'Bilinmeyen veritabanı. Veritabanı ismini kontrol edin',
1050 => 'Zaten var olan bir tabloyu yeniden oluşturamazsınız',
1051 => 'Bilinmeyen tablo ismi. Sql cümleciğini kontrol edin',
1054 => 'Bilinmeyen kolon (sütun) ismi. Sql cümleciğini kontrol edin',
1062 => 'Daha önceden zaten varolan bir kayıt eklenemez',
1064 => 'Sorgu çalıştırılamadı. Sql cümleciğini kontrol edin',
1115 => 'Bilinmeyen karakter seti. Sql cümleciğini kontrol edin',
1136 => 'Kolon sayısı ile değer sayısı eşleşmiyor',
1146 => 'Bilinmeyen tablo ismi. Sql cümleciğini kontrol edin',
1193 => 'Bilinmeyen sistem değişkeni',
1227 => 'Erişim engellendi. Bu işlem için gerekli yetkiniz yok',
1292 => 'Yanlış bir değer girilmeye çalışıyor',
1364 => 'Varsayılan değere sahip olması gereken bir alan var',
1366 => 'Girilmeye çalışılan verilerden birisi sayısal değil',
1406 => 'Girilmeye çalışılan verilerden birisi gereğinden fazla uzun',

// Client Error Codes and Messages
2000 => 'Bilinmeyen MySQL hatası',
2003 => 'Veritabanı sunucusuna bağlanılamadı. Adresi kontrol edin',
2005 => 'Bilinmeyen veritabanı sunucusu. Adresi kontrol edin'
);

if( isset($hata[$errNo]) ) {
return $hata[$errNo];
}

return 'Tanımlanmamış bir hata oluştu. Hata no: '.$errNo;
}


/**
* yolu verilen dosyaya veri yazar
*
* @access protected
* @param string dosya yolu
* @param string dosyaya yazılacak veri
*/
protected function _dosyayaKaydet($yol, $veri)
{
$fp = fopen($yol, 'a');
chmod($yol, 0777);

if( flock($fp, LOCK_EX)) {
fwrite($fp, $veri);
flock($fp, LOCK_UN);
}

chmod($yol, 0750);
fclose($fp);
}


/**
* zaman ölçümleri yapmak için kullanılır
*
* @access protected
*/
protected function _timer()
{
return microtime(true);
}


/**
* bu sınıfı o anda hangi sayfanın kullandığını belirler
*
* @access protected
* @return string geçerli sayfanın yolu
*/
protected function _phpSelf()
{
$yol = strip_tags($_SERVER['PHP_SELF']);
$yol = substr($yol, 0, 200);
$yol = htmlentities(trim($yol), ENT_QUOTES);

return $yol;
}

}//sınıf sonu

?>
