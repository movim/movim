<?php
  
/**
 * This class is by Hans Anderson, me@hansanderson.com
 *
 * This is a stable release, 1.0.  I don't foresee any changes, but you
 * might check http://www.hansanderson.com/php/xml/ to see
 * usage: $xml = xmlize($array);
 *
 * @package jaxl
 * @subpackage core
 * @author Hans Anderson <me@hansanderson.com>
 * @copyright Hans Anderson
 * @link http://www.hansanderson.com/php/xml/
*/

    /**
     * XML to Array convertor class
    */
    class XML {
    
        /*
         * This is a part of standard xmlize class
         * DO NOT TOUCH
        */
        function XML() {
            $this->valid = false;
        }
    
        /*
         * This is a part of standard xmlize class
         * DO NOT TOUCH
        */
        function xmlize($data, $WHITE=1, $encoding='UTF-8') {
            $data = trim($data);
            $vals = $index = $array = array();
            $parser = xml_parser_create($encoding);
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, $WHITE);
            $this->valid = xml_parse_into_struct($parser, $data, $vals, $index);
            xml_parser_free($parser);
            
            $i = 0;
            
            $tagname = $vals[$i]['tag'];
            if(isset($vals[$i]['attributes'])) {
                $array[$tagname]['@'] = $vals[$i]['attributes'];
            } 
            else {
                $array[$tagname]['@'] = array();
            }
            $array[$tagname]["#"] = $this->xml_depth($vals, $i);
            return $array;
        }
    
        /*
         * This is a part of standard xmlize class
         * DO NOT TOUCH
        */
        function xml_depth($vals,&$i) {
            $children = array();
      
            if (isset($vals[$i]['value'])) {
                array_push($children, $vals[$i]['value']);
            }
      
            while (++$i < count($vals)) {
                switch ($vals[$i]['type']) {
                    case 'open':
                        if (isset($vals[$i]['tag'])) {
                            $tagname = $vals[$i]['tag'];
                        } 
                        else {
                            $tagname = '';
                        }
            
                        if (isset($children[$tagname])) {
                            $size = sizeof($children[$tagname]);
                        } 
                        else {
                            $size = 0;
                        }

                        if ( isset ( $vals[$i]['attributes'] ) ) {
                            $children[$tagname][$size]['@'] = $vals[$i]["attributes"];
                        }
            
                        $children[$tagname][$size]['#'] = $this->xml_depth($vals, $i);
                        break;
          
                    case 'cdata':
                        array_push($children, $vals[$i]['value']);
                        break;
          
                    case 'complete':
                        $tagname = $vals[$i]['tag'];
                        if(isset($children[$tagname])) {
                            $size = sizeof($children[$tagname]);
                        } 
                        else {
                            $size = 0;
                        }
            
                        if(isset($vals[$i]['value'])) {
                            $children[$tagname][$size]["#"] = $vals[$i]['value'];
                        } 
                        else {
                            $children[$tagname][$size]["#"] = '';
                        }
            
                        if(isset($vals[$i]['attributes'])) {
                            $children[$tagname][$size]['@'] = $vals[$i]['attributes'];
                        }
                        break;
            
                    case 'close':
                        return $children;
                        break;
                }
            }
            return $children;
        }
    
        /*
         * This is a part of standard xmlize class
         * DO NOT TOUCH
        */
        function traverse_xmlize($array, $arrName = "array", $level = 0) {
            foreach($array as $key=>$val) {
                if(is_array($val)) {
                    $this->traverse_xmlize($val,$arrName."[".$key."]",$level + 1);
                } 
                else {
                    $GLOBALS['traverse_array'][] = '$'.$arrName.'['.$key.']="'.$val."\"\n";
                }
            }
            return 1;
        }
    
    }

?>
