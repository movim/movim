<div id="main">
	<?php $this->widget('Tabs');?>
    <div id="center" >
		<div class="fixed_block">
			<h1 class="paddedtopbottom">
                <i class="fa fa-cogs"></i> <?php echo __('page.administration'); ?>
            </h1>
		</div>
		<div>
			<?php $this->widget('AdminMain');?>
			<?php $this->widget('AdminDB');?>
			<?php $this->widget('AdminTest');?>
			<?php $this->widget('Statistics');?>
			<?php $this->widget('Api');?>
		</div>
    </div>
</div>
