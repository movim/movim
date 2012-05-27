<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>MOVIM</title>
		<link rel="shortcut icon" href="../themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="../themes/movim/css/style2.css" type="text/css" />
	</head>
	<body>
		<div id="content" style="width: 900px">
            <div id="left" style="width: 230px; padding-top: 10px;">

            </div>
            <div id="center" style="padding: 20px;" >
                <h1><?php echo t('Movim Installer')." - ".t('Success !'); ?></h1>

                <div class="valid">
                    - <?php echo t('Valid Bosh'); ?><br />
                    - <?php echo t('Database Detected'); ?><br />
                    - <?php echo t('Database Movim schema installed'); ?><br />
                </div>
                <div class="warning">
                    <?php echo t('You can now access your shiny Movim instance %sJump In !%s', '<a class="button tiny" style="float: right;" href="../index.php">', '</a>');?><br /><br /><br />
                    - <?php echo t('Please remove the %s folder in order to complete the installation', 'install/'); ?>
                </div>
            </div>
		</div>
    </body>
</html>
