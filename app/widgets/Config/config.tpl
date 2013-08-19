<div class="tabelem padded" title="{$c->t('Configuration')}" id="config" >
    <a 
        class="button color orange icon user"
        href="{$c->route('nodeconfig', array($me,'urn:xmpp:microblog:0'))}" 
        style="float: right;">
        {$c->t('Feed Configuration')}
    </a>
    <div class="clear"></div>
    <form enctype="multipart/form-data" method="post" action="index.php" name="general">
        <fieldset>
            <legend>{$c->t('General')}</legend>
            <div class="element">
                <label for="language">{$c->t('Language')}</label>
                <div class="select">
                    <select name="language" id="language">
                        <option value="en">English (default)</option>
                            {loop="languages"}
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
            <div class="element">
                <label>{$c->t('Enable the chatbox ?')}</label>
                <div class="checkbox">
                    <input type="checkbox" id="chatbox" name="chatbox" {$chatbox}/>
                    <label for="chatbox"></label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{$c->t('Appearence')}</legend>
            <div class="element">
                <label for="color">{$c->t('Background color')}</label>                        
                <a 
                    type="button" 
                    onclick="
                        document.querySelector('input[name=color]').value = '082D50';
                        document.body.style.backgroundColor = '#082D50';"
                    style="width: 45%; float: right;" 
                    class="button icon color purple back">
                    {$c->t('Reset')}
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
                <label for="size">{$c->t('Font size')}</label>
                <a 
                    type="button" 
                    onclick="
                        var slide = document.querySelector('input[name=size]')
                        slide.value = 14;
                        slide.onchange();"
                    style="width: 30%; float: right;" 
                    class="button icon color purple back">
                    {$c->t('Reset')}
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
                <label for="pattern">{$c->t('Pattern')}</label>
                
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
            {$c->t('Submit')}
        </a>
        <!--<a type="reset" value="{$c->t('Reset'); ?>" class="button icon no merged left" style="float: right;">-->
        </p>
    </form>
    <br /><br />
    <div class="message info">{$c->t("This configuration is shared wherever you are connected !")}</div>
</div>
