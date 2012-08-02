
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
				<legend><?php echo $steps[$step]; ?></legend>
					<p>
						<input type="hidden" name="step" value="2" />
					</p>
					
					<p <?php echo generate_Tooltip(t("Set the logging options.")); ?>>
						<label for="dbsystem"><?php echo t('Database System to use:'); ?></label>
						<select id="dbsystem" name="dbsystem" onchange="changeDB(this);">		
							<?php 
								$dbsystems = array("mysql", "sqlite", "mongodb"); 
								foreach($dbsystems as $dbsystem):
							?>
								<option value="<?php echo $dbsystem; ?>">
									<?php if(isset($_POST['dbystem']) && $_POST['dbsystem'] == $dbsystem):?>
										selected="selected"
									<?php endif;?>
									<?php echo $dbsystem; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</p>
			</fieldset>
			<fieldset id="dbform">
				
			</fieldset>
			<?php include('buttons.php'); ?>
			<br />
		</form>
	
</div>
