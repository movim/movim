<?php

/**
 * @file Lazy.php
 * This file is part of Movim.
 * 
 * @brief Refresh only parts of the new page
 *
 * @author Timothée jaussoin
 */

class Lazy {
    private $_current;
    private $_next;
    private $_widgets = array();
    
    public function __construct($current, $next) {
        $this->_current = $current;
        $this->_next    = $next;
        
        // We grab the widgets from the current view
        $current_path = VIEWS_PATH . '/' . $this->_current . '.tpl';
        require_once $current_path;
        ob_clean();
        
        $current_widgets = $this->_widgets;
        $this->_widgets = array();
        
        // We grab the widgets from the next view
        $next_path = VIEWS_PATH . '/' . $this->_next . '.tpl';
        require_once $next_path;
        ob_clean();
        
        $next_widgets = $this->_widgets;
        $this->_widgets = array();
        
        // We compare the two lists
        $diff_widgets_current = array_diff($next_widgets, $current_widgets);
        $diff_widgets_next = array_diff($current_widgets, $next_widgets);
        
        \movim_log($diff_widgets_current);
        \movim_log($diff_widgets_next);
        
        $widgets = WidgetWrapper::getInstance(false);
        
        foreach($diff_widgets as $key => $name) {
            RPC::call('movim_fill', strtolower($name) . '_widget', $widgets->runWidget($name, 'build'));
        }
        
        RPC::commit();
    }
    
    private function widget($name) {
        array_push($this->_widgets, $name);
    }
}
