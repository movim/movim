<?php

/**
 * @file i18n.php
 * This file is part of MOVIM.
 * 
 * @brief A collection of functions to translate strings.
 *
 * @author Etenil <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 22 December 2010
 *
 * Copyright (C)2010 MOVIM team.
 * 
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$language = "";
$translations = array();

/**
 * Translates strings into the given langage.
 */
function t($string)
{
	global $language;
	global $translations;

	if(isset($translations[$string])) {
		return $translations[$string];
	}

	return $string;
}

function get_quoted_string($string)
{
	$matches = array();
	preg_match('#"(.+)"#', $string, $matches);

	return $matches[1];
}

/**
 * Parses a .po file.
 */
function parse_lang_file($pofile)
{
	if(!file_exists($pofile)) {
		return false;
	}

	// Parsing the file.
	$handle = fopen($pofile, 'r');

	$trans_string = array();

	$msgid = "";
	$msgstr = "";

	$last_token = "";

	while($line = fgets($handle)) {
		if($line[0] == "#" || trim(rtrim($line)) == "") {
			continue;
		}
		
		if(preg_match('#^msgid#', $line)) {
			if($last_token == "msgstr") {
				$trans_string[$msgid] = $msgstr;
			}
			$last_token = "msgid";
			$msgid = get_quoted_string($line);
		}
		else if(preg_match('#^msgstr#', $line)) {
			$last_token = "msgstr";
			$msgstr = get_quoted_string($line);
		}
		else {
			$$last_token .= get_quoted_string($line);
		}
	}
	if($last_token == "msgstr") {
		$trans_string[$msgid] = $msgstr;
	}
	
	fclose($handle);

	return $trans_string;
}

/**
 * Loads the given language.
 */
function load_language($lang)
{
	global $translations;
	global $language;

	if($lang == $language) {
		return true;
	}

	$translations = parse_lang_file(BASE_PATH . '/i18n/' . $lang . '.po');

	$language = $lang;

	return true;
}

/**
 * Loads a .po file and adds the translations to the existing ones.
 * Conflicting translation strings will be rejected.
 */
function load_extra_lang($directory)
{
	global $translations;
	global $language;
	
	// Converting to unix path (simpler and portable.)
	$directory = str_replace('\\', '/', $directory);

	if($directory[-1] != '/') {
		$directory .= '/';
	}

	$trans = parse_lang_file($directory . $language . '.po');

	if(!$trans) {
		return false;
	}

	// Merging the arrays. The existing translations have priority.
	foreach($trans as $msgid => $msgstr) {
		if(array_key_exists($msgid, $translations)) {
			continue;
		}
		$translations[$msgid] = $msgstr;
	}

	return true;
}

/**
 * Return an array containing all the presents languages in i18n/
 * 
 */

function load_lang_array() {

	$lang_list = array(
		'aa' => "Afar",
		'ab' => "Abkhazian",
		'af' => "Afrikaans",
		'am' => "Amharic",
		'an' => "Aragon&#233;s",
		'ar' => "&#1593;&#1585;&#1576;&#1610;",
		'as' => "Assamese",
		'ast' => "asturianu",
		'ay' => "Aymara",
		'az' => "&#1040;&#1079;&#1241;&#1088;&#1073;&#1072;&#1112;&#1209;&#1072;&#1085;",
		'ba' => "Bashkir",
		'be' => "&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1110;",
		'ber_tam' => "Tamazigh",
		'ber_tam_tfng' => "Tamazigh tifinagh",
		'bg' => "&#1073;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;",
		'bh' => "Bihari",
		'bi' => "Bislama",
		'bm' => "Bambara",
		'bn' => "Bengali; Bangla",
		'bo' => "Tibetan",
		'br' => "brezhoneg",
		'bs' => "bosanski",
		'ca' => "catal&#224;",
		'co' => "corsu",
		'cpf' => "Kr&eacute;ol r&eacute;yon&eacute;",
		'cpf_dom' => "Krey&ograve;l",
		'cpf_hat' => "Kr&eacute;y&ograve;l (P&eacute;yi Dayiti)",
		'cs' => "&#269;e&#353;tina",
		'cy' => "Cymraeg",	# welsh, gallois
		'da' => "dansk",
		'de' => "Deutsch",
		'dz' => "Bhutani",
		'el' => "&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;",
		'en' => "English",
		'en_hx' => "H4ck3R",
		'en_sm' => "Smurf",
		'eo' => "Esperanto",
		'es' => "Espa&#241;ol",
		'es_co' => "Colombiano",
		'et' => "eesti",
		'eu' => "euskara",
		'fa' => "&#1601;&#1575;&#1585;&#1587;&#1609;",
		'ff' => "Fulah", // peul
		'fi' => "suomi",
		'fj' => "Fiji",
		'fo' => "f&#248;royskt",
		'fon' => "fongb&egrave;",
		'fr' => "Fran&#231;ais",
		'fr_sc' => "schtroumpf",
		'fr_lpc' => "langue parl&#233;e compl&#233;t&#233;e",
		'fr_lsf' => "langue des signes fran&#231;aise",
		'fr_spl' => "fran&#231;ais simplifi&#233;",
		'fr_tu' => "fran&#231;ais copain",
		'fy' => "Frisian",
		'ga' => "Irish",
		'gd' => "Scots Gaelic",
		'gl' => "galego",
		'gn' => "Guarani",
		'grc' => "&#7944;&#961;&#967;&#945;&#943;&#945; &#7961;&#955;&#955;&#951;&#957;&#953;&#954;&#942;", // grec ancien
		'gu' => "Gujarati",
		'ha' => "Hausa",
		'hbo' => "&#1506;&#1489;&#1512;&#1497;&#1514;&#1470;&#1492;&#1514;&#1504;&#1498;", // hebreu classique ou biblique
		'he' => "&#1506;&#1489;&#1512;&#1497;&#1514;",
		'hi' => "&#2361;&#2367;&#2306;&#2342;&#2368;",
		'hr' => "hrvatski",
		'hu' => "magyar",
		'hy' => "Armenian",
		'ia' => "Interlingua",
		'id' => "Indonesia",
		'ie' => "Interlingue",
		'ik' => "Inupiak",
		'is' => "&#237;slenska",
		'it' => "italiano",
		'it_fem' => "italiana",
		'iu' => "Inuktitut",
		'ja' => "&#26085;&#26412;&#35486;",
		'jv' => "Javanese",
		'ka' => "&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312;",
		'kk' => "&#2325;&#2379;&#2306;&#2325;&#2339;&#2368;",
		'kl' => "kalaallisut",
		'km' => "Cambodian",
		'kn' => "Kannada",
		'ko' => "&#54620;&#44397;&#50612;",
		'ks' => "Kashmiri",
		'ku' => "Kurdish",
		'ky' => "Kirghiz",
		'la' => "lingua latina",
		'lb' => "L&euml;tzebuergesch",
		'ln' => "Lingala",
		'lo' => "&#3742;&#3762;&#3754;&#3762;&#3749;&#3762;&#3751;", # lao
		'lt' => "lietuvi&#371;",
		'lu' => "luba-katanga",
		'lv' => "latvie&#353;u",
		'man' => "mandingue", # a traduire en mandingue
		'mfv' => "manjak", # ISO-639-3
		'mg' => "Malagasy",
		'mi' => "Maori",
		'mk' => "&#1084;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080; &#1112;&#1072;&#1079;&#1080;&#1082;",
		'ml' => "Malayalam",
		'mn' => "Mongolian",
		'mo' => "Moldavian",
		'mos' => "Mor&eacute;",
		'mr' => "&#2350;&#2352;&#2366;&#2336;&#2368;",
		'ms' => "Bahasa Malaysia",
		'mt' => "Maltese",
		'my' => "Burmese",
		'na' => "Nauru",
		'nap' => "napulitano",
		'ne' => "Nepali",
		'nqo' => "N'ko", // www.manden.org
		'nl' => "Nederlands",
		'no' => "norsk",
		'nb' => "norsk bokm&aring;l",
		'nn' => "norsk nynorsk",
		'oc' => "&ograve;c",
		'oc_lnc' => "&ograve;c lengadocian",
		'oc_ni' => "&ograve;c ni&ccedil;ard",
		'oc_ni_la' => "&ograve;c ni&ccedil;ard (larg)",
		'oc_prv' => "&ograve;c proven&ccedil;au",
		'oc_gsc' => "&ograve;c gascon",
		'oc_lms' => "&ograve;c lemosin",
		'oc_auv' => "&ograve;c auvernhat",
		'oc_va' => "&ograve;c vivaroaupenc",
		'om' => "(Afan) Oromo",
		'or' => "Oriya",
		'pa' => "Punjabi",
		'pbb' => 'Nasa Yuwe',
		'pl' => "polski",
		'ps' => "Pashto, Pushto",
		'pt' => "Portugu&#234;s",
		'pt_br' => "Portugu&#234;s do Brasil",
		'qu' => "Quechua",
		'rm' => "Rhaeto-Romance",
		'rn' => "Kirundi",
		'ro' => "rom&#226;n&#259;",
		'roa' => "ch'ti",
		'ru' => "&#1088;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;",
		'rw' => "Kinyarwanda",
		'sa' => "&#2360;&#2306;&#2360;&#2381;&#2325;&#2371;&#2340;",
		'sc' => "sardu",
		'scn' => "sicilianu",
		'sd' => "Sindhi",
		'sg' => "Sangho",
		'sh' => "srpskohrvastski",
		'sh_latn' => 'srpskohrvastski',
		'sh_cyrl' => '&#1057;&#1088;&#1087;&#1089;&#1082;&#1086;&#1093;&#1088;&#1074;&#1072;&#1090;&#1089;&#1082;&#1080;',
		'si' => "Sinhalese",
		'sk' => "sloven&#269;ina",	// (Slovakia)
		'sl' => "sloven&#353;&#269;ina",	// (Slovenia)
		'sm' => "Samoan",
		'sn' => "Shona",
		'so' => "Somali",
		'sq' => "shqip",
		'sr' => "&#1089;&#1088;&#1087;&#1089;&#1082;&#1080;",
		'src' => 'sardu logudor&#233;su', // sarde cf 'sc'
		'sro' => 'sardu campidan&#233;su',
		'ss' => "Siswati",
		'st' => "Sesotho",
		'su' => "Sundanese",
		'sv' => "svenska",
		'sw' => "Kiswahili",
		'ta' => "&#2980;&#2990;&#3007;&#2996;&#3021;", // Tamil
		'te' => "Telugu",
		'tg' => "Tajik",
		'th' => "&#3652;&#3607;&#3618;",
		'ti' => "Tigrinya",
		'tk' => "Turkmen",
		'tl' => "Tagalog",
		'tn' => "Setswana",
		'to' => "Tonga",
		'tr' => "T&#252;rk&#231;e",
		'ts' => "Tsonga",
		'tt' => "&#1058;&#1072;&#1090;&#1072;&#1088;",
		'tw' => "Twi",
		'ty' => "reo m&#257;`ohi", // tahitien
		'ug' => "Uighur",
		'uk' => "&#1091;&#1082;&#1088;&#1072;&#1111;&#1085;&#1089;&#1100;&#1082;&#1072;",
		'ur' => "&#1649;&#1585;&#1583;&#1608;",
		'uz' => "U'zbek",
		'vi' => "Ti&#7871;ng Vi&#7879;t",
		'vo' => "Volapuk",
		'wa' => "walon",
		'wo' => "Wolof",
		'xh' => "Xhosa",
		'yi' => "Yiddish",
		'yo' => "Yoruba",
		'za' => "Zhuang",
		'zh' => "&#20013;&#25991;", // chinois (ecriture simplifiee)
		'zh_tw' => "&#21488;&#28771;&#20013;&#25991;", // chinois taiwan (ecr. traditionnelle)
		'zu' => "Zulu"

	);
	$dir = scandir(BASE_PATH . '/i18n/');
	$po = array();
	foreach($dir as $files) {
		$explode = explode('.', $files);
		if(end($explode) == 'po') {
			$po[$explode[0]] = $lang_list[$explode[0]];
			//array_push($po, $explode[0] => $lang_list[$explode[0]]);
		}
	}
	
	return $po;
}


?>
