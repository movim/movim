<?php /* -*- mode: html -*- */
    $cd = new \Modl\ConfigDAO();
    $config = $cd->get();
?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title><?php $this->title();?></title>
    
    <meta name="description" content="<?php echo $config->description; ?>" />
    
    <link rel="shortcut icon" href="<?php $this->linkFile('img/favicon.ico');?>" />
	<link rel="stylesheet" href="<?php echo BASE_URI; ?>app/assets/js/leaflet.css" />
	<script src="<?php echo BASE_URI; ?>app/assets/js/leaflet.js"></script>
    
    <meta name="viewport" content="width=device-width, user-scalable=no">

    <?php
        $this->addCss('css/animations.css');

        $this->addCss('css/fonts.css');
        $this->addCss('css/forms.css');
        $this->addCss('css/submitform.css');

        $this->addCss('css/nav.css');

        $this->addCss('css/style.css');
        $this->addCss('css/posts.css');

        $this->addCss('css/template.css');

        $this->scripts();

        $this->addCss('css/mobile.css');
         
        $this->addCss('css/font-awesome.css'); 
    
        $user = new User();

        $color = $user->getConfig('color');
        
        if(isset($color)) {
            echo '
            <style type="text/css">
                body, nav {
                    background-color: #'.$color.';
                    animation: none;
                    -webkit-animation: none;
                }
            </style>';
        }

        $size = $user->getConfig('size');    
        if(isset($size)) {
            echo '
            <style type="text/css">
                body { font-size: '.$size.'px; }
            </style>';
        }
    ?>
    <?php $this->widget('System');?>
  </head>
    <body>
        <noscript>
            <style type="text/css">
                nav {display:none;} #content {display: none;}
            </style>
            <div class="warning" style="width: 500px; margin: 0 auto;">
            <?php echo __('global.no_js'); ?>
            </div>
        </noscript>
        <nav>
            <div class="wrapper">
                <?php $this->menu();?>
            </div>
        </nav>
        
        <!--<div id="baseline"></div>-->

        <div id="content">
            <?php $this->widget('Notification');?>
            <?php $this->widget('Ack');?>
            <?php $this->content();?>
          
            <footer>
                © <a href="http://www.movim.eu">Movim</a> • 2008 - 2014 • Under <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License</a>
            </footer>
        </div>
        <script type="text/javascript">
            movim_onload();
        </script>
        <?php 
            $this->displayFooterDebug();
        ?>
    </body>
</html>
