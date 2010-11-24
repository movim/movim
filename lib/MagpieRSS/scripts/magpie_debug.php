<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
define('MAGPIE_DIR', '../');
define('MAGPIE_DEBUG', 1);

// flush cache quickly for debugging purposes, don't do this on a live site
define('MAGPIE_CACHE_AGE', 10);

require_once(MAGPIE_DIR.'rss_fetch.inc');


if ( isset($_GET['url']) ) {
	$url = $_GET['url'];
}
else {
	$url = 'http://magpierss.sf.net/test.rss';
}


test_library_support();

$rss = fetch_rss( $url );
	
if ($rss) {
	echo "<h3>Example Output</h3>";
	echo "Channel: " . $rss->channel['title'] . "<p>";
	echo "<ul>";
	foreach ($rss->items as $item) {
		$href = $item['link'];
		$title = $item['title'];	
		echo "<li><a href=$href>$title</a></li>";
	}
	echo "</ul>";
}
else {
	echo "Error: " . magpie_error();
}
?>

<form>
	RSS URL: <input type="text" size="30" name="url" value="<?php echo $url ?>"><br />
	<input type="submit" value="Parse RSS">
</form>

<h3>Parsed Results (var_dump'ed)</h3>
<pre>
<?php var_dump($rss); ?>
</pre>

<?php

function test_library_support() {
   if (!function_exists('xml_parser_create')) {
	   echo "<b>Error:</b> PHP compiled without XML support (--with-xml), Mapgie won't work without PHP support for XML.<br />\n";
	   exit;
   }
   else {
	   echo "<b>OK:</b> Found an XML parser. <br />\n";
   }
   
   if ( ! function_exists('gzinflate') ) {
	   echo "<b>Warning:</b>  PHP compiled without Zlib support (--with-zlib). No support for GZIP encoding.<br />\n";
   }
   else {
	   echo "<b>OK:</b>  Support for GZIP encoding.<br />\n";
   }
   
   if ( ! (function_exists('iconv') and function_exists('mb_convert_encoding') ) ) {
	   echo "<b>Warning:</b>  No support for iconv (--with-iconv) or multi-byte strings (--enable-mbstring)." .  
		   "No support character set munging.<br />\n";
   }
   else {
	   echo "<b>OK:</b>  Support for character munging.<br />\n";
   }
}

?>
