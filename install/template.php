<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Movim | Installer</title>
		<link rel="shortcut icon" href="../themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="../themes/movim/css/style2.css" type="text/css" />
		<script type="text/javascript">
          function changeDB(select)
          {
			var type = select.selectedIndex;
			var fieldset = document.getElementById('dbform');
			fieldset.innerHTML = '';
			/**
            var dbspec = document.getElementById("database");
            switch(type) {
            case "sqlite":
              dbspec.value = "sqlite:///movim.db";
              break;
            case "mysql":
              dbspec.value = "mysql://username:password@host:<?php echo get_mysql_port(); ?>/database";
              break;
            default:
              dbspec.value = "db://username:password@host:<?php echo get_mysql_port();?>port/database";
            }***/
          }
        </script>
	</head>
	<body>
		<div id="content" style="width: 900px">
			
			<?php require("part".$step.".php"); ?>
			
		</div>
    </body>
</html>

