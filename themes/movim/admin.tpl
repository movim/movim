<div id="head">
</div>

<div id="main" style="margin-top: 0px;">
    <div id="left">

    </div>
    <div id="center" >
		<div class="fixed_block">
			<h1><?php echo t('Administration Panel'); ?></h1>
			<?php $this->widget('Tabs');?>
		</div>
		<div class="moving_block" >
			<?php $this->widget('Admin');?>
		</div>
    </div>
</div>
