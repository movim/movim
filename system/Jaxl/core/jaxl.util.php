<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2010, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Abhinav Singh nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package jaxl
 * @subpackage core
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl
 */

    /**
     * Jaxl Utility Class
    */
    class JAXLUtil {
        
        public static function curl($url, $type='GET', $headers=false, $data=false, $user=false, $pass=false) {
            $ch = curl_init($url);
            
            // added by Movim Project
            if(defined('JAXL_CURL_ASYNC') && JAXL_CURL_ASYNC) curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, false);
            if($headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            
            if($type == 'POST') {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            
            if($user && $pass) {
                curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            }
            
            $rs = array();
            $rs['content'] = curl_exec($ch);
            $rs['errno'] = curl_errno($ch);
            $rs['errmsg'] = curl_error($ch);
            $rs['header'] = curl_getinfo($ch);
            
            curl_close($ch);
            return $rs;
        }
        
        public static function isWin() {
            return strtoupper(substr(PHP_OS,0,3)) == "WIN" ? true : false;
        }
        
        public static function pcntlEnabled() {
            return extension_loaded('pcntl');
        }
        
        public static function sslEnabled() {
            return extension_loaded('openssl');
        }
        
        public static function getTime() {
            list($usec, $sec) = explode(" ", microtime());
            return (float) $sec + (float) $usec;
        }
        
        public static function splitXML($xml) {
            $xmlarr = array();
            $temp = preg_split("/<(message|iq|presence|stream|proceed|challenge|success|failure)(?=[\:\s\>])/", $xml, -1, PREG_SPLIT_DELIM_CAPTURE);
                for($a=1; $a<count($temp); $a=$a+2) $xmlarr[] = "<".$temp[$a].$temp[($a+1)];
            return $xmlarr;
        }

        public static function explodeData($data) {
            $data = explode(',', $data);
            $pairs = array();
            $key = false;
            
            foreach($data as $pair) {
                $dd = strpos($pair, '=');
                if($dd) {
                    $key = trim(substr($pair, 0, $dd));
                    $pairs[$key] = trim(trim(substr($pair, $dd + 1)), '"');
                }
                else if(strpos(strrev(trim($pair)), '"') === 0 && $key) {
                    $pairs[$key] .= ',' . trim(trim($pair), '"');
                    continue;
                }
            }
            
            return $pairs;
        }
        
        public static function implodeData($data) {
            $return = array();
            foreach($data as $key => $value)
                $return[] = $key . '="' . $value . '"';
            return implode(',', $return);
        }
        
        public static function generateNonce() {
            $str = '';
            mt_srand((double) microtime()*10000000);
            for($i=0; $i<32; $i++)
                $str .= chr(mt_rand(0, 255));
            return $str;
        }
        
        public static function encryptPassword($data, $user, $pass) {
            foreach(array('realm', 'cnonce', 'digest-uri') as $key)
                if(!isset($data[$key]))
                    $data[$key] = '';
            
            $pack = md5($user.':'.$data['realm'].':'.$pass);
            
            if(isset($data['authzid'])) 
                $a1 = pack('H32',$pack).sprintf(':%s:%s:%s',$data['nonce'],$data['cnonce'],$data['authzid']);
            else 
                $a1 = pack('H32',$pack).sprintf(':%s:%s',$data['nonce'],$data['cnonce']);
            $a2 = 'AUTHENTICATE:'.$data['digest-uri'];
            
            return md5(sprintf('%s:%s:%s:%s:%s:%s', md5($a1), $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], md5($a2)));
        }

        public static function hmacMD5($key, $data) {
            if(strlen($key) > 64) $key = pack('H32', md5($key));
            if(strlen($key) < 64) $key = str_pad($key, 64, chr(0));
            $k_ipad = substr($key, 0, 64) ^ str_repeat(chr(0x36), 64);
            $k_opad = substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64);
            $inner  = pack('H32', md5($k_ipad . $data));
            $digest = md5($k_opad . $inner);
            return $digest;
        }
        
        public static function pbkdf2($data, $secret, $iteration, $dkLen=32, $algo='sha1') {
            $hLen = strlen(hash($algo, null, true));
            
            $l = ceil($dkLen/$hLen);
            $t = null;
            for($i=1; $i<=$l; $i++) {
                $f = $u = hash_hmac($algo, $s.pack('N', $i), $p, true);
                for($j=1; $j<$c; $j++)
                    $f ^= ($u = hash_hmac($algo, $u, $p, true));
                $t .= $f;
            }
            return substr($t, 0, $dk_len);
        }
        
        public static function getBareJid($jid) {
            list($user,$domain,$resource) = self::splitJid($jid);
            return ($user ? $user."@" : "").$domain;
        }
        
        public static function splitJid($jid) {
            preg_match("/(?:([^\@]+)\@)?([^\/]+)(?:\/(.*))?$/",$jid,$matches);
            return array($matches[1],$matches[2],@$matches[3]);
        }

        /*
         * xmlentities method for PHP supporting
         * 1) Rserved characters in HTML
         * 2) ISO 8859-1 Symbols
         * 3) ISO 8859-1 Characters
         * 4) Math Symbols Supported by HTML
         * 5) Greek Letters Supported by HTML
         * 6) Other Entities Supported by HTML
         *
         * Credits:
         * --------
         * http://www.sourcerally.net/Scripts/39-Convert-HTML-Entities-to-XML-Entities
         * http://www.w3schools.com/tags/ref_entities.asp
         * http://www.w3schools.com/tags/ref_symbols.asp
        */
        public static function xmlentities($str) {
            $str = htmlentities($str, ENT_QUOTES, 'UTF-8');
            $xml = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;','&#8704;','&#8706;','&#8707;','&#8709;','&#8711;','&#8712;','&#8713;','&#8715;','&#8719;','&#8721;','&#8722;','&#8727;','&#8730;','&#8733;','&#8734;','&#8736;','&#8743;','&#8744;','&#8745;','&#8746;','&#8747;','&#8756;','&#8764;','&#8773;','&#8776;','&#8800;','&#8801;','&#8804;','&#8805;','&#8834;','&#8835;','&#8836;','&#8838;','&#8839;','&#8853;','&#8855;','&#8869;','&#8901;','&#913;','&#914;','&#915;','&#916;','&#917;','&#918;','&#919;','&#920;','&#921;','&#922;','&#923;','&#924;','&#925;','&#926;','&#927;','&#928;','&#929;','&#931;','&#932;','&#933;','&#934;','&#935;','&#936;','&#937;','&#945;','&#946;','&#947;','&#948;','&#949;','&#950;','&#951;','&#952;','&#953;','&#954;','&#955;','&#956;','&#957;','&#958;','&#959;','&#960;','&#961;','&#962;','&#963;','&#964;','&#965;','&#966;','&#967;','&#968;','&#969;','&#977;','&#978;','&#982;','&#338;','&#339;','&#352;','&#353;','&#376;','&#402;','&#710;','&#732;','&#8194;','&#8195;','&#8201;','&#8204;','&#8205;','&#8206;','&#8207;','&#8211;','&#8212;','&#8216;','&#8217;','&#8218;','&#8220;','&#8221;','&#8222;','&#8224;','&#8225;','&#8226;','&#8230;','&#8240;','&#8242;','&#8243;','&#8249;','&#8250;','&#8254;','&#8364;','&#8482;','&#8592;','&#8593;','&#8594;','&#8595;','&#8596;','&#8629;','&#8968;','&#8969;','&#8970;','&#8971;','&#9674;','&#9824;','&#9827;','&#9829;','&#9830;');
            $html = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&forall;','&part;','&exist;','&empty;','&nabla;','&isin;','&notin;','&ni;','&prod;','&sum;','&minus;','&lowast;','&radic;','&prop;','&infin;','&ang;','&and;','&or;','&cap;','&cup;','&int;','&there4;','&sim;','&cong;','&asymp;','&ne;','&equiv;','&le;','&ge;','&sub;','&sup;','&nsub;','&sube;','&supe;','&oplus;','&otimes;','&perp;','&sdot;','&Alpha;','&Beta;','&Gamma;','&Delta;','&Epsilon;','&Zeta;','&Eta;','&Theta;','&Iota;','&Kappa;','&Lambda;','&Mu;','&Nu;','&Xi;','&Omicron;','&Pi;','&Rho;','&Sigma;','&Tau;','&Upsilon;','&Phi;','&Chi;','&Psi;','&Omega;','&alpha;','&beta;','&gamma;','&delta;','&epsilon;','&zeta;','&eta;','&theta;','&iota;','&kappa;','&lambda;','&mu;','&nu;','&xi;','&omicron;','&pi;','&rho;','&sigmaf;','&sigma;','&tau;','&upsilon;','&phi;','&chi;','&psi;','&omega;','&thetasym;','&upsih;','&piv;','&OElig;','&oelig;','&Scaron;','&scaron;','&Yuml;','&fnof;','&circ;','&tilde;','&ensp;','&emsp;','&thinsp;','&zwnj;','&zwj;','&lrm;','&rlm;','&ndash;','&mdash;','&lsquo;','&rsquo;','&sbquo;','&ldquo;','&rdquo;','&bdquo;','&dagger;','&Dagger;','&bull;','&hellip;','&permil;','&prime;','&Prime;','&lsaquo;','&rsaquo;','&oline;','&euro;','&trade;','&larr;','&uarr;','&rarr;','&darr;','&harr;','&crarr;','&lceil;','&rceil;','&lfloor;','&rfloor;','&loz;','&spades;','&clubs;','&hearts;','&diams;');
            $str = str_replace($html,$xml,$str);
            $str = str_ireplace($html,$xml,$str);
            return $str;
        }
    }
    
?>
