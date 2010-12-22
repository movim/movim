<?php /* -*- mode: html -*- */
?><!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php $this->title();?></title>
    <link rel="shortcut icon" href="<?php $this->link_file('img/favicon.ico');?>" />
    <?php $this->addCss('css/style2.css');?>
    <?php $this->addCss('css/login_form.css');?>
    <?php $this->scripts();?>
  </head>
<body>
	<div id="nav">
	  <?php $this->menu();?>
	</div>
	<div id="content">
	  <?php $this->content();?>
	  	<div id="footer">
			© <a href="http://www.movim.eu">Movim</a> - 2010 | Under <a href="http://www.gnu.org/licenses/agpl-3.0.html">GNU Affero General Public License</a>
		</div>
	</div>

  </body>
</html>
