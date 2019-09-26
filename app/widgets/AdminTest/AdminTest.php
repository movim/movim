<?php

use Movim\Widget\Base;

class AdminTest extends Base
{
    public function load()
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

    public function display()
    {
        // Check with Eloquent or delete
        $this->view->assign('dbconnected', true);
    }
}
