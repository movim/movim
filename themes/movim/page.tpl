<?php /* -*- mode: html -*- */
?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title><?php $this->title();?></title>

    <meta name="viewport" content="target-densitydpi=device-dpi, initial-scale=1.1, user-scalable=no" />
          
    <link rel="shortcut icon" href="<?php $this->link_file('img/favicon.ico');?>" />
	<link rel="stylesheet" href="<?php echo BASE_URI; ?>app/assets/js/leaflet.css" />
	<script src="<?php echo BASE_URI; ?>app/assets/js/leaflet.js"></script>
    
    <script type="text/javascript">
        var BASE_URI = '<?php echo BASE_URI; ?>';
        var ERROR_URI = '<?php echo substr_replace(Route::urlize('disconnect', 'err'), '', -3); ?>';
        var PAGE_KEY_URI = '<?php 
            if(!$_SERVER['HTTP_MOD_REWRITE'])
                echo '?q='; ?>'
    </script>
    <?php
    $this->addCss('css/animations.css');
    $this->addCss('css/forms.css');
    $this->addCss('css/submitform.css');

    $this->addCss('css/posts.css');
    $this->addCss('css/style2.css');

    $this->scripts();
    
    $this->addCss('css/mobile.css'); 
    
    $user = new User();

    $color = $user->getConfig('color');
    
    $pattern = $user->getConfig('pattern');
    if(!isset($pattern))
        $pattern = 'empty';
    
    if(isset($color)) {
        echo '
        <style type="text/css">
            body { background-color: #'.$color.'; }
        </style>';
    }

    $size = $user->getConfig('size');    
    if(isset($size)) {
        echo '
        <style type="text/css">
            body { font-size: '.$size.'px; }
        </style>';
    }

    /*$this->addCss('css/animations.css');
    $this->addCss('css/forms.css');
    
    if(preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT']))
    { 
        echo '
            <meta content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" name="viewport" />
            <meta name="viewport" content="width=device-width" />
            ';
        $this->addCss('css/mobile.css'); 
    } else { 
        $this->addCss('css/posts.css');
        $this->addCss('css/style2.css');
    }

    $this->scripts();
    
    $user = new User();

    $color = $user->getConfig('color');
    if(isset($color)) {
        echo '
        <style type="text/css">
            body { background-color: #'.$color.'; }
        </style>';
    }*/
    ?>

  </head>
    <body onload="movim_onload();" class="<?php echo $pattern; ?>">
        <noscript>
            <style type="text/css">
                #nav {display:none;} #content {display: none;}
            </style>
            <div class="warning" style="width: 500px; margin: 0 auto;">
            <?php echo t("You don't have javascript enabled.  Good luck with that."); ?>
            </div>
        </noscript>
        <nav>
            <?php $this->menu();?>	
        </nav>
        
        <!--<div id="baseline"></div>-->

        <div id="content">
            <?php $this->widget('Notification');?>
            <?php $this->content();?>
          
            <footer>
                © <a href="http://www.movim.eu">Movim</a> • 2008 - 2013 • Under <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License</a>
            </footer>
        </div>

    </body>
</html>
