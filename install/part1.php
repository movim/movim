
<div id="left" style="width: 230px; padding-top: 10px;">
	
	<?php include("error.php"); ?>
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
						<input type="hidden" name="step" value="1" />
					</p>
					<p <?php echo generate_Tooltip("So far there is only one theme. :-)"); ?>>
						<label for="movim" ><?php echo t('Theme'); ?></label>
							<select id="theme" name="theme">
								<?php
									foreach(list_themes() as $key=>$value)
									echo '<option value="'.$key.'"'.((get_preset_value('theme', 'movim') == $value)? ' selected="selected"': '').'>'.$value.'</option>';
								?>
							</select>
					</p>
	
					<p <?php echo generate_Tooltip(t("Movim is already tranlsated in a lot of languages. <br>Some translations are unfortunately incomplete you could help translating on launchpad")); ?>>
						<label for="da" ><?php echo t('Default language'); ?></label>
						<select id="defLang" name="defLang">
							<?php
								foreach(list_lang() as $key=>$value)
									echo '<option value="'.$key.'"'.((get_preset_value('defLang', 'English') == $key)? ' selected="selected"': '').'>'.$value.'</option>';
							?>
			
						</select>
					</p>
					<p <?php echo generate_Tooltip(t("Movim can limit the maximum allowed amounts of registered users. If you do not want this feature leave the value \'-1\'.")); ?>>
					  <label for="maxUsers"><?php echo t('Maximum population'); ?></label>
					  <input type="text" name="maxUsers" id="maxUsers" value="<?  echo get_preset_value('maxUsers', -1); ?>" />
					</p>
					
					<p <?php echo generate_Tooltip(t("Set the logging options.")); ?>>
						<?php
							$logopts = array(
							0 => t('empty'),
							2 => t('terse'),
							4 => t('normal'),
							6 => t('talkative'),
							7 => t('ultimate'),
							);
							$default_log = 4;
						?>
	
						<label for="7"><?php echo t("Log verbosity"); ?></label>
						<select id="logLevel" name="logLevel">
							<?php foreach($logopts as $lognum => $text):?>
								<option value="<?php echo $lognum;?>"
									<?php if(get_preset_value('logLevel', $default_log) == $lognum):?>
										selected="selected"
									<?php endif;?>>

									<?php echo $text;?>
								</option>
							<?php endforeach; ?>
						</select>
					</p>
			</fieldset>
			<?php include('buttons.php'); ?>
			<br />
		</form>
	
</div>
