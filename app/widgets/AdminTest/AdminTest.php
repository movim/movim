<?php

class AdminTest extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('admintest.js');
        $this->addcss('admintest.css');
    }

    public function testDir($dir)
    {
        return (file_exists($dir) && is_dir($dir) && is_writable($dir));
    }

    public function testFile($file)
    {
        return (file_exists($file) && is_writable($file));
    }

    function display()
    {
        // Check with Eloquent or delete
        $this->view->assign('dbconnected', true);
    }
}
