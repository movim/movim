<?php
namespace system;
class Debug
{
    static function getDump($var, $maxDepth = 2, $stripTags = false)
    {
        $var = self::export($var, $maxDepth++,$stripTags);
        if ($stripTags) {
            return var_export($var,true);
        } else {
            return '<div class="xdebug-var-dump" style="font-size:13px;padding:5px;margin:0;clear:both;z-index:9999;background-color:white;"><ul>'.self::getDisplayDump($var).'</ul></div>';
        }
    }
    /**
     * Prints a dump of the public, protected and private properties of $var.
     *
     * @param mixed $var
     * @param integer $maxDepth Maximum nesting level for object properties
     * @param boolean $stripTags Flag that indicate if output should strip HTML tags
     */
    static function dump($var, $maxDepth = 2, $stripTags = false)
    {
        $var = self::export($var, $maxDepth++,$stripTags);
        if ($stripTags) {
           print  var_export($var,true);
        } else {
            
            print '<div class="xdebug-var-dump" style="font-size:13px;padding:5px;margin:0;clear:both;z-index:9999;background-color:white;"><ul>'.self::getDisplayDump($var).'</ul></div>';
        }
    }

    public static function getDisplayDump($dump)
    {
        $return = '';
        if (is_array($dump['value'])) { 
            $return .= $dump['type'];
            $return .= '<ul style="list-style-type:none;padding-left:20px;">';

            foreach ($dump['value'] as $i =>$val) {
                $return .= '<li>';
                $return .='<span style="font-weight:bold;">'.$i.'</span> => '.self::getDisplayDump($val);

                $return .= '</li>';
            }
            $return .= '</ul>';
        } else {
            if (is_null($dump['value'])) {
                $return .= $dump['type'];
            } else {
                
                $return .= $dump['type'] .' "'.  htmlentities(substr($dump['value'],0,200)).'"';
            }
            
        }
        return $return;
    }
    

    /**
     * Export
     *
     * @param mixed $var
     * @param int $maxDepth
     * @return mixed
     */
    public static function export($var, $maxDepth = 2,$striptags = false)
    {
         $aReturn = array('type' => '', 'value' => $var);
        if ($striptags) {
            //text only version
            if (is_array($var)) {
                $aReturn['type'] = ' Array (length ' . count($var) . ') ';
            } else if (is_object($var)) {
                $aReturn['type'] = 'Object (class ' . get_class($var) . ') ';
            } else {
                $aReturn['type'] = gettype($var);
            }
            if ($maxDepth === 0) {
                if (is_object($var) || is_array($var)) {
                   return $aReturn['type'];
                } else {
                    return $aReturn['value'];
                }
            }
        } else {
            //html version
            
            if (is_array($var)) {
                $aReturn['type'] = '<small>Array</small> <small><em style="color:red">(length ' . count($var) . ')</em></small>';
            } else if (is_object($var)) {
                $aReturn['type'] = '<small>Object</small> <small><em style="color:green">(class ' . get_class($var) . ')</em></small>';
            } else {
                $aReturn['type'] = '<small>'.gettype($var).'</small>';
            }
            if ($maxDepth === 0) {
                if (is_object($var) || is_array($var)) {
                    $aReturn['value'] = null;
                }
                return $aReturn;
            }
        }

        if (is_array($var)) {
            $aReturn['value'] = array();

            foreach ($var as $k => $v) {
                $aReturn['value'][$k] = self::export($v, $maxDepth - 1,$striptags);
            }
        } else if (is_object($var)) {
            $aReturn['value'] = array();
            $var = self::getProperties($var);
            foreach ($var as $k => $v) {
                $aReturn['value'][$k] = self::export($v, $maxDepth - 1,$striptags);
            }
        } else {
            $aReturn['value'] = $var;
        }

        if ($striptags) {
            return $aReturn['value'];
        }
        return $aReturn;
    }

    protected static function getProperties($object)
    {
        $class = get_class($object);
        $resArray = array();
        $reflection = new ReflectionObject($object);
        $properties = $reflection->getProperties();
        foreach ($properties as $attr) {
            $attr->setAccessible(true);
            $resArray[implode(' ', Reflection::getModifierNames($attr->getModifiers())) . ' $' . $attr->name] = $attr->getValue($object);
        }
        return $resArray;
    }

}

?>
