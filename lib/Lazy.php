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
    
    public function __construct($current, $next) {
        $this->_current = $current;
        $this->_next    = $next;
        
        
    }
}
