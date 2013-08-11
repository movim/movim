<div id="head">
</div>

<div id="main" style="margin-top: 0px;">
    <div id="left">

    </div>
    <div id="center" >
		<div class="fixed_block" style="position: fixed; background-color: white; width: 880px; z-index:10">
			<h1><?php echo t('Administration Panel'); ?></h1>
			<?php $this->widget('Tabs');?>
		</div>
		<div class="moving-block" >
			<?php $this->widget('Admin');?>
		</div>
    </div>
</div>
