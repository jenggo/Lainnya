<?php
    /* Ganti variabel dibawah sesuai yang diinginkan */
        $namadb = './dbsqlite.db';
        $titleurl = 'url.jenggo.net';
        $alamaturl = 'http://url.jenggo.net';
    /* Setelah baris ini hampir tidak ada yang perlu diubah */

    //Cek apakah database sudah ada
    $db = new SQLiteDatabase($namadb);

    //Cek apakah tabel sudah ada
    $cekdb = @$db->query('SELECT nomor FROM url');

    //Tidak ditemukan tabel, mari kita buat baru!
    if (!$cekdb) {
        $buatabel = $db->query('CREATE TABLE url (nomor INTEGER PRIMARY KEY, shorturl CHAR(5), realurl CHAR(255))');
        if (!$buatabel)
            die('Tidak bisa membuat database!');
    }

    /* Ada yang memanggil versi pendek..! */
    if (isset($_GET['url']) AND !empty($_GET['url'])) {
        $shorturl = RemoveXSS($_GET['url']); //Filter dulu

        //Cek apakah shorturl tersebut ada dalam database?
        $urlpanjang = $db->singlequery("SELECT realurl FROM url WHERE shorturl='$shorturl'");

        //Jika ada buang ke realurl, jika tidak biarkan saja
        if ($urlpanjang) {
            header('X-Powered-By: '.$titleurl.'/0.1');
            header('Location: '.$urlpanjang);
        }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $titleurl; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
</head>

<body>
 <div id="wrap">
    <div id="content">
        <?php
            //Jika ada yang ingin membuat shorturl :
            if (isset($_POST['bikin_pendek']) AND !empty($_POST['situs'])) {
                $realurl = RemoveXSS($_POST['situs']); //Filter input!
                $realurl = 'http://'.str_replace('http://', '', $realurl); //Pastikan agar http:// disertakan! Sori, belum mendukung protokol https

                $cekurl = @get_meta_tags($realurl);

                if (!$cekurl) {
                    echo 'Tidak ada situs dengan alamat '.$realurl.' !';
                }
                else {
                    //Cek apakah url tersebut sudah ada..
                    $cek = @$db->singlequery("SELECT nomor FROM url WHERE realurl='$realurl'");

                    if ($cek)
                        echo 'Sudah ada yang memasukkan url tersebut!';
                    else {
                        $shorturl = buat_karakter(5); //Buat karakter random
                        while(!cekduplikat($shorturl, $namadb)) //Buat karakter random baru sampai tidak ditemukan dalam database..
                            $pendek = buat_karakter(5);

                        //Jika sudah, kita masukkan data kedalam database :
                        $buat = $db->query("INSERT INTO url (shorturl, realurl) VALUES ('$shorturl', '$realurl')");

                        echo ($db) ? $alamaturl.'/'.$shorturl : 'Gagal membuat shorturl!';
                    }
                }
            }

            //Tampilkan form pengisian bila tidak ada input :
            else {
                ?>
                <form method="post" action="">
                    <label>Situs</label>
                    <input type="text" name="situs" class="teks" />
                    <input type="submit" name="bikin_pendek" value="Pendekin!" class="tombol" />
                </form>
                <?php
            }
        ?>
    </div>
 </div>

 <div id="footer">
    &copy; <?php echo date('Y').', '.$titleurl; ?></a>
 </div>

</body>
</html>
<?php
    /* Fungsi-fungsi PHP yang diperlukan.. Biar tidak perlu menulis lagi untuk proyek selanjutnya... Haha.. */

    //Buat karakter random..
    function buat_karakter($panjang = 4) {
        $i = 0;
        $karakter = '123456789abcdefghijklmnopqrstuvwxyz';
        $url = '';

        while($i<$panjang) {
            $random = mt_rand(1, 34);
            $url .= $karakter[$random];
            $i++;
        }
        return $url;
    }

    //Cek apakah shorturl sudah ada dalam database
    function cekduplikat($karakter, $namadb) {
        $db = new SQLiteDatabase($namadb);
        $cek = $db->query("SELECT nomor FROM url WHERE shorturl='$karakter'");
        return ($cek) ? true : false;
    }

    //Fungsi untuk membersihkan input dari XSS dan sql injection (diambil dari SNews - http://www.snewscms.com)
    $XSS_cache = array();
    $ra1 = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html',
             'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
    $ra2 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script',
            'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base',
            'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy',
            'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint',
            'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick',
            'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged',
            'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave',
            'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus',
            'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload',
            'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover',
            'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange',
            'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit',
            'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart',
            'onstop', 'onsubmit', 'onunload');
    $tagBlacklist = array_merge($ra1, $ra2);

    function cleanSQL($query) {
        if (get_magic_quotes_gpc()) $query = stripslashes($query);
        $query = mysql_real_escape_string($query);
        return $query;
    }

    function RemoveXSS($val) {
        if ($val != "") {
                global $XSS_cache;
                if (!empty($XSS_cache) && array_key_exists($val, $XSS_cache)) return $XSS_cache[$val];
                $source = html_entity_decode($val, ENT_QUOTES, 'ISO-8859-1');
                $source = preg_replace('/&#38;#(\d+);/me','chr(\\1)', $source);
                $source = preg_replace('/&#38;#x([a-f0-9]+);/mei','chr(0x\\1)', $source);
                while($source != filterTags($source)) {
                        $source = filterTags($source);
                }
                $source = nl2br($source);
                $XSS_cache[$val] = $source;
                return $source;
        }
        return cleanSQL($val);
    }

    function filterTags($source) {
        global $tagBlacklist;
        $preTag = NULL;
        $postTag = $source;
        $tagOpen_start = strpos($source, '<');
        while($tagOpen_start !== FALSE) {
                $preTag .= substr($postTag, 0, $tagOpen_start);
                $postTag = substr($postTag, $tagOpen_start);
                $fromTagOpen = substr($postTag, 1);
                $tagOpen_end = strpos($fromTagOpen, '>');
                if ($tagOpen_end === false) break;
                $tagOpen_nested = strpos($fromTagOpen, '<');
                if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
                        $preTag .= substr($postTag, 0, ($tagOpen_nested+1));
                        $postTag = substr($postTag, ($tagOpen_nested+1));
                        $tagOpen_start = strpos($postTag, '<');
                        continue;
                }
                $tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
                $currentTag = substr($fromTagOpen, 0, $tagOpen_end);
                $tagLength = strlen($currentTag);
                if (!$tagOpen_end) {
                        $preTag .= $postTag;
                        $tagOpen_start = strpos($postTag, '<');
                }
                $tagLeft = $currentTag;
                $attrSet = array();
                $currentSpace = strpos($tagLeft, ' ');
                if (substr($currentTag, 0, 1) == '/') {
                        $isCloseTag = TRUE;
                        list($tagName) = explode(' ', $currentTag);
                        $tagName = substr($tagName, 1);
                } else {
                        $isCloseTag = FALSE;
                        list($tagName) = explode(' ', $currentTag);
                }
                if ((!preg_match('/^[a-z][a-z0-9]*$/i',$tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $tagBlacklist)))) {
                        $postTag = substr($postTag, ($tagLength + 2));
                        $tagOpen_start = strpos($postTag, '<');
                        continue;
                }
                while ($currentSpace !== FALSE) {
                        $fromSpace = substr($tagLeft, ($currentSpace+1));
                        $nextSpace = strpos($fromSpace, ' ');
                        $openQuotes = strpos($fromSpace, '"');
                        $closeQuotes = strpos(substr($fromSpace, ($openQuotes+1)), '"') + $openQuotes + 1;
                        if (strpos($fromSpace, '=') !== FALSE) {
                                if (($openQuotes !== FALSE) && (strpos(substr($fromSpace, ($openQuotes+1)), '"') !== FALSE))
                                        $attr = substr($fromSpace, 0, ($closeQuotes+1));
                                        else $attr = substr($fromSpace, 0, $nextSpace);
                        } else $attr = substr($fromSpace, 0, $nextSpace);
                        if (!$attr) $attr = $fromSpace;
                                $attrSet[] = $attr;
                                $tagLeft = substr($fromSpace, strlen($attr));
                                $currentSpace = strpos($tagLeft, ' ');
                }
                $postTag = substr($postTag, ($tagLength + 2));
                $tagOpen_start = strpos($postTag, '<');
        }
        $preTag .= $postTag;
        return $preTag;
    }
?>