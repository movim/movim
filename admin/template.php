<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?php echo $title; ?></title>
		<link rel="shortcut icon" href="../themes/movim/img/favicon.ico" />
		<link rel="stylesheet" href="../themes/movim/css/style2.css" type="text/css" />
		<link rel="stylesheet" href="../themes/movim/css/forms.css" type="text/css" />
        <style type="text/css">
            #left { padding-top: 10px; }
            #content {margin: 0 auto; margin-top: 100px; }
        </style>
		<script type="text/javascript">
          function changeDB(select)
          {
			var type = select.selectedIndex;
			var options = select.options;
			option = options[type].text;
			switch(option) {
				case "sqlite":
					displayDB('sqlite', options);
					break;
				case "mysql":
					displayDB("mysql", options);
					break;
				case "mongo":
					break;
				default: ;
		}
		function displayDB(which, options){
			for(i=0; i<options.length; i++){
				elemnt = document.getElementById(options[i].text);
				if(options[i].text == which){
					elemnt.style.display = 'block';
				}else{
					elemnt.style.display = 'none';
				}
			}
		}
			}
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
          
        </script>
	</head>
    <body>
        <div id="nav">
          
        </div>

        <div id="content">
			
			<?php require("part".$display.".php"); ?>
			
		</div>
    </body>
</html>

