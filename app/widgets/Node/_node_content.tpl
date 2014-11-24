<div class="posthead paddedtopbottom">
    {if="$subscribed == true"}  
        <a
            href="#" 
            class="button color"
            onclick="movim_toggle_display('#groupunsubscribe')">
            <i class="fa fa-arrow-left"></i> {$c->__('node.unsubscribe')}
        </a>
    {else}
        <a 
            href="#" 
            class="button color green"
            onclick="movim_toggle_display('#groupsubscribe')">
            <i class="fa fa-arrow-right"></i> {$c->__('node.subscribe')}
        </a>
    {/if}
    <a 
        class="button color merged left" 
        href="{$c->route('blog',array($serverid,$groupid))}"
        target="_blank"
    >
        <i class="fa fa-pencil"></i> {$c->__('page.blog')}
    </a><a 
        class="button color orange alone merged right" 
        href="{$c->route('feed',array($serverid,$groupid))}"
        target="_blank"
    ><i class="fa fa-rss"></i></a>
    <a
        href="#"
        onclick="{$refresh}
        this.className='button color alone orange'; this.onclick=null;"
        class="button color blue alone">
        <i class="fa fa-refresh"></i>
    </a>

    <!--
    <a 
        class="button color icon yes"
        onclick="{$getsubscription}"
    >{$c->t('Get Subscription')}</a>
    -->
    <a 
        class="button color oppose"
        style="display: none;"
        id="configbutton"
        href="{$c->route('nodeconfig', array($serverid,$groupid))}"
    ><i class="fa fa-user"></i> {$c->__('page.configuration')}</a>
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
