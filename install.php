<?php

function testInstall($test, $description = '', $advice = '') {
	$html = '<tr><td>';
		$conf = new GetConf();
		$theme = $conf->getServerConfElement('theme');
		if($test) {
			$html.= '<img src="themes/'.$theme.'/img/accept.png"></td>
					<td>'.$description.'</td>';
		} else {
			$html .= '<img src="themes/'.$theme.'/img/delete.png"></td>
					<td>'.$description.'</td>';
			$html .= '<td>'.$advice.'</td>';
			define('INSTALL_VALIDATED', false);
		}
	$html .= '</tr>';
	echo $html;
	
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>MOVIM</title>
		<link rel="shortcut icon" href="themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="themes/movim/css/style2.css" type="text/css" />
	</head>
	<body>
		<div id="content">
			<h1><?php echo t('Compatibility Test'); ?></h1>
			<center><p><?php echo t('Until these items will not be validated, MOVIM will not run properly on your server'); ?></p></center>
			<table style="margin: 0 auto;">
				<tr>
					<th style="width: 20px;"></th>
					<th style="width: 400px;"></th>
					<th></th>
				</tr>
				<?php
					
					$v = explode( '.', phpversion());

					testInstall($v['0'] >= 5 && $v['1'] >= 3, 
								t('PHP version > 5.3'), 
								t('Activate PHP5 on your server or update it to >5.3'));

					testInstall(extension_loaded('curl'), 
								t('CURL extension present'), 
								t('Install php5-libcurl package or turn on Curl extension on your host server'));

					testInstall(extension_loaded('SimpleXML'),
								t('SimpleXML extension present'),
								t('Install php5 package'));
							
					testInstall(is_writable('user/'), 
								t('User folder is writable'),
								'Change folder rights');

					testInstall(is_writable('log/'), 
								t('Log folder is writable'), 
								'Change folder rights');

					if(!defined('INSTALL_VALIDATED') && INSTALL_VALIDATED != false) {
						echo '<tr><td></td>
								<td><div class="valid">'. t('You have validated all the tests, now you can switch the "install" variable to 0 in config/conf.xml') .'</div></td>
							</tr>
						
							';
					}
				?>
			</table>
		</div>
	</body>

</html>

