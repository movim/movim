<div id="left" style="width: 230px; padding-top: 10px;">
	<div class="warning" id="leftside">
		<p><?php echo t('Move your mousepointer over the configure options to receive more information.');?></p>
	</div>
</div>
<div id="center" style="padding: 20px;" >
	<h1 style="padding: 10px 0px;"><?php echo $title; ?></h1>
	<br>
		<form method="post" action="index.php">
			<fieldset>
				<legend><?php echo $steps[$display]; ?></legend>
					<p>
						<input type="hidden" name="step" value="4" />
					</p>
					<p <?php echo generate_Tooltip(t("Enter the XMPP-Server here: ")); ?>>
						<label for="xmppServer"><?php echo t("XMPP-Server"); ?></label>
						<input type="text" id="xmppServer" name="xmppServer" value="<? echo get_preset_value('xmppServer', ''); ?>"/>
					</p>
			</fieldset>
			<?php include('buttons.php'); ?>
			<br />
		</form>
	
</div>

