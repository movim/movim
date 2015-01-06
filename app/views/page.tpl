<?php /* -*- mode: html -*- */
    $cd = new \Modl\ConfigDAO();
    $config = $cd->get();
?><!DOCTYPE html>
<html ng-app="roster">
  <head>
    <meta charset="utf-8" />
    <title><?php $this->title();?></title>
    
    <meta name="description" content="<?php echo $config->description; ?>" />
    
    <link rel="shortcut icon" href="<?php $this->linkFile('img/favicon.ico');?>" />
    <link rel="stylesheet" href="<?php echo BASE_URI; ?>app/assets/js/leaflet.css" />
    <script src="<?php echo BASE_URI; ?>app/assets/js/leaflet.js"></script>
    
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    
    <meta name="viewport" content="width=device-width, user-scalable=no">

    <?php
        $this->addCss('style.css');
        $this->addCss('list.css');
        $this->addCss('grid.css');
        $this->addCss('article.css');
        $this->addCss('form.css');
        $this->addCss('color.css');
        $this->addCss('block.css');
        $this->addCss('menu.css');
        $this->addCss('material-design-iconic-font.min.css');
        /*$this->addCss('css/animations.css');

        $this->addCss('css/fonts.css');
        $this->addCss('css/forms.css');
        $this->addCss('css/submitform.css');

        $this->addCss('css/nav.css');

        $this->addCss('css/style.css');
        $this->addCss('css/posts.css');

        $this->addCss('css/template.css');
        */
        $this->widget('System');

        $this->scripts();
        /*
        $this->addCss('css/mobile.css');
         
        $this->addCss('css/font-awesome.css'); 
    
        $user = new User();

        $color = $user->getConfig('color');
        
        if(isset($color)) {
            echo '
            <style type="text/css">
                nav {
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
                @media screen and (max-width: 1024px) {
                    body { font-size: '.floor($size*1.15).'px; }
                }
            </style>';
        }*/
    ?>
    </head>
    <body class="<?php $this->color();?>">
        <noscript>
            <style type="text/css">
                nav {display:none;} #content {display: none;}
            </style>
            <div class="warning" style="width: 500px; margin: 0 auto;">
            <?php echo __('global.no_js'); ?>
            </div>
        </noscript>
        <div id="snackbar" class="snackbar"></div>
        <?php $this->widget('Dialog');?>
        <?php $this->widget('Notification');?>
        <?php $this->content();?>

        <!--
        <nav>
            <div class="wrapper">
                <?php //$this->menu();?>
            </div>
        </nav>-->
        
        <!--<div id="baseline"></div>-->

        <!--
        <div id="content">
            <?php //$this->widget('Ack');?>
            <?php //$this->content();?>
          
            <footer>
                © <a href="http://www.movim.eu">Movim</a> • 2008 - 2014 • Under <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License</a>
            </footer>
        </div>-->
        <?php 
            $this->displayFooterDebug();
        ?>
        <script type="text/javascript">movim_onload();</script>
    </body>
</html>
