<?php
    require('init.php');
    $pd = new \modl\PostDAO();
    $pd->create();
    
    $nd = new \modl\NodeDAO();
    $nd->create();
    
    $cd = new \modl\ContactDAO();
    $cd->create();
    
    $cad = new \modl\CapsDAO();
    $cad->create();
    
    $prd = new \modl\PresenceDAO();
    $prd->create();
    
    $rd = new \modl\RosterLinkDAO();
    $rd->create();
    
    $sd = new \modl\SessionDAO();
    $sd->create();
    
    $cd = new \modl\CacheDAO();
    $cd->create();
    
    $md = new \modl\MessageDAO();
    $md->create();

echo 'Recreate database... done !';
