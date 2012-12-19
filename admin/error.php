<?php if($errors){ ?>
<div class="message error"><?php echo t('The following requirements were not met. Please make sure they are all satisfied in order to install Movim.'); ?><br /><br />
	<?php
        foreach($errors as $error) {
          ?>
          <p class="error"><?php echo $error;?></p>
          <?php
        }
?>
</div>
<?php
} ?>
