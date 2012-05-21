<?php /* -*- mode: html -*- */
?>
<style type="text/css">
html {
    height: 100%;
}

body {
    background-image: radial-gradient(center
     45deg, circle closest-corner, #ffffff 0%, #717171 100%);
    background-image: -moz-radial-gradient(center
     45deg, ellipse, #6E9EA8 0%, #274950 100%);
    background-repeat: no-repeat;
    height: auto;
    display: block;
    vertical-align: middle;
}
.account_button {
	margin: 2em auto;
	width: 300px;
	font-size: 1.6em;
}
h1 {
	color: white;
	display: block;
	margin: 0 auto;
}
</style>

<div id="center">
	<br />
	<h1><?php echo t('Make your choice !');?></h1>
	<a href="?q=accountCreate">
		<div class="account_button button big green"><?php echo t('Create a new account'); ?></div>
	</a>
	<a href="?q=accountAdd">
		<div class="account_button button big green"><?php echo t('Link my actual account'); ?></div>
	</a>
</div>

