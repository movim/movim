<div id="head">
</div>

<div id="main" style="margin-top: 0px;">

	<?php $this->widget('Tabs');?>
    <div id="center" >
		<div class="fixed_block">
			<h1><?php echo t('Administration Panel'); ?></h1>
		</div>
		<div class="moving_block" >
			<?php $this->widget('Admin');?>
		</div>
    </div>
</div>
