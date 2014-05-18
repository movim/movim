<div class="tabelem padded" title="{$c->__('page.configuration')}" id="config" >
    <a 
        class="button color orange icon user"
        href="{$c->route('nodeconfig', array($me,'urn:xmpp:microblog:0'))}" 
        style="float: right;">
        {$c->__('config.feed_configuration')}
    </a>
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <fieldset>
            <legend>{$c->__('config.general')}</legend>
            <div class="element">
                <label for="language">{$c->__('config.language')}</label>
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
                <label for="color">{$c->__('config.background_color')}</label> 
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
                <label for="size">{$c->__('config.font_size')}</label>
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
            
            <div class="element large">
                <label for="pattern">{$c->__('button.reset')}</label>
                
                <input type="radio" name="pattern" id="argyle" value="argyle"/>
                <label for="argyle"><span></span>
                    <div class="preview argyle"
                        style="background-color: #6d695c;"></div>
                </label>
                
                <input type="radio" name="pattern" id="default" value="default"/>
                <label for="default"><span></span>
                    <div class="preview default"
                        style="background-color: #082D50;;"></div>
                </label>
                
                <input type="radio" name="pattern" id="tableclothe" value="tableclothe"/>
                <label for="tableclothe"><span></span>
                    <div class="preview tableclothe"
                        style="background-color: rgba(200, 0, 0, 1);"></div>
                </label>
                
                <input type="radio" name="pattern" id="blueprint" value="blueprint"/>
                <label for="blueprint"><span></span>
                    <div class="preview blueprint"
                        style="background-color:#269;"></div>
                </label>
                
                <input type="radio" name="pattern" id="cicada" value="cicada"/>
                <label for="cicada"><span></span>
                    <div class="preview cicada"
                        style="background-color: #026873;"></div>
                </label>
                
                <input type="radio" name="pattern" id="stripes" value="stripes"/>
                <label for="stripes"><span></span>
                    <div class="preview stripes"
                        style="background-color: orange;"></div>
                </label>
                
                <input type="radio" name="pattern" id="stars" value="stars"/>
                <label for="stars"><span></span>
                    <div class="preview stars"
                        style="background-color:black; background-size: 100px 100px;"></div>
                </label>
                
                <input type="radio" name="pattern" id="paper" value="paper"/>
                <label for="paper"><span></span>
                    <div class="preview paper"
                        style="background-color: #23343E;"></div>
                </label>
                
                <input type="radio" name="pattern" id="tartan" value="tartan"/>
                <label for="tartan"><span></span>
                    <div class="preview tartan"
                        style="background-color: hsl(2, 57%, 40%);"></div>
                </label>
                
                <input type="radio" name="pattern" id="empty" value=""/>
                <label for="empty"><span></span>
                    <div class="preview empty"
                        style="background-color: white;"></div>
                </label>
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
            class="button icon yes color green" 
            style="float: right;">
            {$c->__('button.submit')}
        </a>
        <div class="clear"></div>
    </form>
    <div class="message info">{$c->__('config.info')}</div>
</div>
