<div class="tabelem padded" title="{$c->__('page.configuration')}" id="config" >
    <a 
        class="button color orange oppose"
        href="{$c->route('nodeconfig', array($me,'urn:xmpp:microblog:0'))}" >
        <i class="fa fa-user"></i> {$c->__('config.feed_configuration')}
    </a>
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <fieldset>
            <legend><i class="fa fa-sliders"></i> {$c->__('config.general')}</legend>
            <div class="element">
                <label for="language"><i class="fa fa-language"></i> {$c->__('config.language')}</label>
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
            </div>
        </fieldset>

        <fieldset>
            <legend>{$c->__('config.appearence')}</legend>
            <div class="element">
                <label for="color"><i class="fa fa-adjust"></i> {$c->__('config.background_color')}</label> 
                <a 
                    type="button" 
                    onclick="
                        document.querySelector('input[name=color]').value = '32434D';
                        document.body.style.backgroundColor = '#32434D';"
                    style="width: 45%; float: right;" 
                    class="button icon color purple back">
                    {$c->__('button.reset')}
                </a>
                <input 
                    style="box-shadow: none; width: 50%; float: left;"
                    name="color"
                    class="color" 
                    onchange="document.body.style.backgroundColor = '#'+this.value;"
                    value="
                    {if="isset($color)"}
                        {$color}
                    {else}
                        082D50
                    {/if}
                    ">
            </div>
            
            <div class="element">
                <label for="size"><i class="fa fa-font"></i> {$c->__('config.font_size')}</label>
                <a 
                    type="button" 
                    onclick="
                        var slide = document.querySelector('input[name=size]')
                        slide.value = 14;
                        slide.onchange();"
                    style="width: 30%; float: right;" 
                    class="button icon color purple back">
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
                        onchange="
                            document.body.style.fontSize = this.value+'px';
                            document.querySelector('#currentsize').innerHTML = this.value+'px'";
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
            
        </fieldset>
        <br />
        
        <hr />
<!--<label id="lock" for="soundnotif">{$c->t('Enable Sound Notification:'); ?></label>
      <input type="checkbox" name="soundnotif" value="soundnotif" checked="checked" /><br /> -->
<!--<input value="{$c->t('Submit'); ?>" onclick="<?php echo $submit; ?>" type="button" class="button icon yes merged right" style="float: right;">
        <input type="reset" value="{$c->t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->

        <br />
        <a 
            onclick="{$submit}" 
            type="button" 
            class="button color green oppose" >
            <i class="fa fa-check"></i> {$c->__('button.submit')}
        </a>
        <div class="clear"></div>
    </form>
    <div class="message info">{$c->__('config.info')}</div>
</div>
