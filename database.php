<?php
    require('init.php');
    $pd = new \modl\PostDAO();
    $pd->create();
    
    $nd = new \modl\NodeDAO();
    $nd->create();
    
    $cd = new \modl\ContactDAO();
    $cd->create();

echo 'Recreate database... done !';
