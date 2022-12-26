<?php if ($this->js_check) { ?>
<noscript>
    <style type="text/css">main {display: none;}</style>
    <ul class="list" style="color: white;">
        <li>
            <div>
                <p class="center"><?php echo __('global.no_js'); ?></p>
            </div>
        </li>
    </ul>
</noscript>
<?php } ?>
<div id="hiddendiv"></div>
<div id="snackbar" class="snackbar"></div>
<div id="status_websocket" class="snackbar hide">
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">signal_cellular_null</i>
            </span>
            <span class="primary icon gray">
                <i class="material-icons">signal_cellular_off</i>
            </span>
            <div>
                <p class="normal line two">
                    <?php echo __('error.websocket_connect'); ?>
                </p>
                <p class="line two">
                    <?php echo __('error.websocket'); ?>
                </p>
            </div>
        </li>
    </ul>
</div>
<?php $this->widget('Dialog');?>
<?php $this->widget('Drawer');?>
<?php $this->widget('Confirm');?>
<?php $this->widget('Preview');?>
<?php $this->widget('Notif');?>
<?php $this->widget('Toast');?>