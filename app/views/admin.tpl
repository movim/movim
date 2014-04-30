<div id="main">
	<?php $this->widget('Tabs');?>
    <div id="center" >
		<div class="fixed_block">
			<h1><?php echo t('Administration Panel'); ?></h1>
		</div>
		<div class="moving_block" >
			<?php $this->widget('Admin');?>
			<?php $this->widget('Statistics');?>
		</div>
    </div>
</div>
