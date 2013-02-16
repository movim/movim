<?php
    require('init.php');
    $pd = new \modl\PostDAO();
    $pd->create();
    
    $nd = new \modl\NodeDAO();
    $nd->create();

echo 'Recreate database... done !';
