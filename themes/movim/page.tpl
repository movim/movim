<?php /* -*- mode: html -*- */
?><!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php $this->title();?></title>
    <link rel="shortcut icon" href="<?php $this->link_file('img/favicon.ico');?>" />
    <?php $this->addCss('css/style2.css');?>
    <?php $this->addCss('css/posts.css');?>
    <?php $this->scripts();?>
  </head>
<body onload="movim_onload()">
	<noscript>
        <style type="text/css">
            #nav {display:none;} #content {display: none;}
        </style>
        <div class="warning" style="width: 500px; margin: 0 auto;">
        <?php echo t("You don't have javascript enabled.  Good luck with that."); ?>
        </div>
    </noscript>
	<div id="nav">
	  <?php $this->menu();?>	
	</div>

	<div id="content">
	  <?php $this->content();?>
	  	<div id="footer">
			© <a href="http://www.movim.eu">Movim</a> - 2011 | Under <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License</a>
		</div>
	</div>

  </body>
</html>
