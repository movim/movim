<?php if($errors){ ?>
<p class="center"><?php echo t('The following requirements were not met. Please make sure they are all satisfied in order to install Movim.'); ?></p><br />
	<?php
                foreach($errors as $error) {
                  ?>
                  <p class="error"><?php echo $error;?></p>
                  <?php
                }
} ?>
