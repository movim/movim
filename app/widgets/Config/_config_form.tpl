<!--<a 
    class="button color orange oppose"
    href="{$c->route('nodeconfig', array($me,'urn:xmpp:microblog:0'))}" >
    <i class="fa fa-user"></i> {$c->__('config.feed_configuration')}
</a>-->
<form enctype="multipart/form-data" method="post" action="index.php" name="general">
    <br/>
    <h3>{$c->__('config.general')}</h3>
    <div class="block">
        <div class="select">
            <select name="language" id="language">
                <option value="en">English (default)</option>
                    {loop="$languages"}
                        {if="$key == $conf"}
                            <option 
                                value="{$key}" 
                                selected="selected">
                                {$value}
                            </option>
                        {else}
                            <option 
                                value="{$key}">
                                {$value}
                            </option>
                        {/if}
                    {/loop}
            </select>
        </div>
        <label for="language">{$c->__('config.language')}</label>
    </div>
    <!--
    <h3>{$c->__('config.appearence')}</h3>-->

    <!--<div class="block large" id="nav_color">
        <a
            type="button"
            style="width: 45%; float: right;" 
            class="button flat">
            {$c->__('button.reset')}
        </a>
        <input 
            style="box-shadow: none; width: 50%; float: left;"
            name="color"
            class="color" 
            value="
            {if="isset($color)"}
                {$color}
            {else}
                082D50
            {/if}
            ">
        <label for="color"><i class="fa fa-adjust"></i> {$c->__('config.background_color')}</label> 
    </div>-->
    <!--
    <div class="block large" id="font_size">
        <label for="size"><i class="fa fa-font"></i> {$c->__('config.font_size')}</label>
        <a 
            type="button"
            class="button flat">
            {$c->__('button.reset')}
        </a>
        <span>
            12
            <input 
                id="slide" 
                type="range" 
                min="12" 
                max="16" 
                step="0.5" 
                value="
                {if="isset($size)"}
                    {$size}
                {else}
                    14
                {/if}
                " 
                name="size"
                style="width: 45%;"
            />
            16
        </span>
        <span id="currentsize">
            {if="isset($size)"}
                {$size}
            {else}
                14
            {/if}
            px
        </span>
    </div>
    -->
    
    
<!--<label id="lock" for="soundnotif">{$c->t('Enable Sound Notification:'); ?></label>
  <input type="checkbox" name="soundnotif" value="soundnotif" checked="checked" /><br /> -->
<!--<input value="{$c->t('Submit'); ?>" onclick="<?php echo $submit; ?>" type="button" class="button icon yes merged right" style="float: right;">
    <input type="reset" value="{$c->t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->

    <div class="clear padded"></div>
    <a 
        onclick="{$submit}" 
        class="button color oppose" >
        {$c->__('button.save')}
    </a>
    <div class="clear"></div>
</form>
<!--
<div class="message info">{$c->__('config.info')}</div>-->
