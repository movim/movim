<div id="left" style="width: 230px; padding-top: 10px;">
	<div class="warning" id="leftside">
		<p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
	</div>
</div>
<div id="center" style="padding: 20px;" >
	<h1 style="padding: 10px 0px;"><?php echo t('Movim Installer'); ?></h1>
	<br>
		<form method="post" action="index.php">
			<fieldset>
				<legend><?php echo $steps[$display]; ?></legend>
					<p>
						<input type="hidden" name="step" value="5" />
					</p>
					<p <?php echo generate_Tooltip(t("Enter here the BOSH-URL in the form: http://domain:123/asd")); ?>>
						<label for="defBoshURL"><?php echo t("Bosh URL"); ?></label>
						<input type="text" id="defBoshURL" name="defBoshURL" value="<? echo get_preset_value('defBoshURL', ''); ?>"/>

					</p>
					
					
			</fieldset>
			<fieldset id="dbform">
				
			</fieldset>
			<?php include('buttons.php'); ?>
			<br />
		</form>
	
</div>

