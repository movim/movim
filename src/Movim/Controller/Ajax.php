<?php
namespace Movim\Controller;

class Ajax extends Base
{
    protected $funclist = [];
    protected static $instance;
    protected $widgetlist = [];

    public function __construct()
    {
        parent::__construct();
    }

    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new Ajax;
        }

        return self::$instance;
    }

    /**
     * Generates the javascript part of the ajax.
     */
    public function genJs()
    {
        if (empty($this->funclist)) {
            return '';
        }
        $buffer = '<script type="text/javascript">';
        foreach ($this->funclist as $key => $funcdef) {
            $parlist = implode(',', $funcdef['params']);

            $buffer .= 'function ' . $funcdef['object'] . '_'
                . $funcdef['funcname'] . "(${parlist}){";

            $function = "MWSs('";
            if ($funcdef['http'] === 1) $function = " return MWSa('";
            if ($funcdef['http'] === 2) $function = " return MWSad('";

            $buffer .=
                $function .
                $funcdef['object'] . "','" .
                $funcdef['funcname'] . "'" .
                (!empty($funcdef['params']) ? ",[${parlist}]" : '');
            $buffer .=")}";
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

        $http = 0;
        if (preg_match('#^ajaxHttp#', $funcname)) $http = 1;
        if (preg_match('#^ajaxHttpDaemon#', $funcname)) $http = 2;

        $this->funclist[$widget.$funcname] = [
            'object' => $widget,
            'funcname' => $funcname,
            'params' => $params,
            'http' => $http
        ];
    }
}
