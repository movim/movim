<?php

/**
 * @file AjaxController.php
 * This file is part of Movim.
 * 
 * @brief Description
 *
 * @author Timothée jaussoin
 *
 * @date  14 November 2013
 */

class AjaxController extends BaseController
{
    protected $funclist = array();
    protected static $instance;
    protected $widgetlist = array();

    public function __construct()
    {
        parent::__construct();
    }

    public static function getInstance()
    {
        if(!is_object(self::$instance)) {
            self::$instance = new AjaxController();
        }
        return self::$instance;
    }

    /**
     * Generates the javascript part of the ajax.
     */
    public function genJs()
    {
        if(empty($this->funclist)) {
            return '';
        }
        
        $buffer = '<script type="text/javascript">';
        foreach($this->funclist as $key => $funcdef) {
            $parlist = implode(', ', $funcdef['params']);

            $buffer .= "function " . $funcdef['object'] . '_'
                . $funcdef['funcname'] . "(${parlist}) {";
            $buffer .= "MovimWebsocket.send('".$funcdef['object']."', '".$funcdef['funcname']."', [${parlist}]);}\n";
        }
        return $buffer . "</script>\n";
    }

    /**
     * Check if the widget is registered
     */
    public function isRegistered($widget)
    {
        return array_key_exists($widget, $this->widgetlist);
    }

    /**
     * Defines a new function.
     */
    public function defun($widget, $funcname, array $params)
    {
        array_push($this->widgetlist, $widget);
        $this->funclist[$widget.$funcname] = array(
            'object' => $widget,
            'funcname' => $funcname,
            'params' => $params,
            );
    }
}

?>
