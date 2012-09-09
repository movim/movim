<p style="padding: 20px 0px;">
	<label for="send">&nbsp;</label>
		<input type="submit" style="float: left" class="button icon back" id="back" name="back" value="<?php echo t('Back'); ?>" <?php echo generate_Tooltip($steps[$display-1], false); ?>/>
		<input type="submit" style="float: right" class="button icon next" id="send" name="send" value="<?php echo t('Next'); ?>" <?php echo generate_Tooltip($steps[$display+1], false); ?>/>
</p>
