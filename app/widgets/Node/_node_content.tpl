<div class="posthead">
    {if="$subscribed == true"}  
        <a
            href="#" 
            class="button color icon back"
            onclick="movim_toggle_display('#groupunsubscribe')">
            {$c->__('node.unsubscribe')}
        </a>
    {else}
        <a 
            href="#" 
            class="button color green icon next"
            onclick="movim_toggle_display('#groupsubscribe')">
            {$c->__('node.subscribe')}
        </a>
    {/if}
    <a 
        class="button color icon blog merged left" 
        href="{$c->route('blog',array($serverid,$groupid))}"
        target="_blank"
    >
        {$c->__('page.blog')}
    </a><a 
        class="button color orange icon alone feed merged right" 
        href="{$c->route('feed',array($serverid,$groupid))}"
        target="_blank"
    ></a>
    <a
        href="#"
        onclick="{$refresh}
        this.className='button icon color alone orange loading'; this.onclick=null;"
        class="button color blue icon alone refresh"></a>

    <!--
    <a 
        class="button color icon yes"
        onclick="{$getsubscription}"
    >{$c->t('Get Subscription')}</a>
    -->
    <a 
        class="button color icon user"
        style="float: right; display: none;"
        id="configbutton"
        href="{$c->route('nodeconfig', array($serverid,$groupid))}"
    >{$c->__('page.configuration')}</a>
</div>

<div class="popup" id="groupsubscribe">
    <form name="groupsubscribe">
        <fieldset>
            <legend>{$c->__('node.subscribe')}</legend>
            <div class="element">
                <label>{$c->__('node.share_label')}</label>                            
                <div class="checkbox">
                    <input type="checkbox" name="listgroup" id="listgroup"/>
                    <label for="listgroup"></label>
                </div>
            </div>
            <div class="element">
                <label for="grouptitle">{$c->__('node.nickname_label')}</label>
                <input type="text" name="title" value="{$groupid}" id="grouptitle"/>
            </div>
        </fieldset>
        <div class="menu">
            <a 
                class="button tiny icon yes black merged left"
                onclick="
                    {$subscribe}
                    this.onclick=null;"
            >{$c->__('node.subscribe')}</a><a 
                class="button tiny icon no black merged right" 
                onclick="
                    movim_toggle_display('#groupsubscribe');"
            >{$c->__('button.close')}</a>
        </div>
    </form>
</div>

<div class="popup" id="groupunsubscribe">
    <form name="groupunsubscribe">
        <fieldset>
            <legend>{$c->__('node.unsubscribe')}</legend>
            <div class="element">
                <label>{$c->__('node.sure')}</label>
            </div>
        </fieldset>
        <div class="menu">
            <a 
                class="button tiny icon yes black merged left"
                onclick="
                    {$unsubscribe}
                    this.onclick=null;"
            >{$c->__('node.unsubscribe')}</a><a 
                class="button tiny icon no black merged right" 
                onclick="
                    movim_toggle_display('#groupunsubscribe');"
            >{$c->__('button.close')}</a>
        </div>
    </form>
</div>

{$submitform}
{$posts}
