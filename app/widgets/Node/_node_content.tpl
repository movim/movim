<div class="posthead">
    {if="$subscribed == true"}  
        <a
            href="#" 
            class="button color icon back"
            onclick="movim_toggle_display('#groupunsubscribe')">
            {$c->t('Unsubscribe')}
        </a>
    {else}
        <a 
            href="#" 
            class="button color green icon next"
            onclick="movim_toggle_display('#groupsubscribe')">
            {$c->t('Subscribe')}
        </a>
    {/if}
    <a 
        class="button color icon blog merged left" 
        href="{$c->route('blog',array($serverid,$groupid))}"
        target="_blank"
    >
        {$c->t('Blog')}
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
    >{$c->t('Configuration')}</a>
</div>

<div class="popup" id="groupsubscribe">
    <form name="groupsubscribe">
        <fieldset>
            <legend>{$c->t('Subscribe')}</legend>
            <div class="element">
                <label>{$c->t('Make your membership to this group public to your friends')}</label>                            
                <div class="checkbox">
                    <input type="checkbox" name="listgroup" id="listgroup"/>
                    <label for="listgroup"></label>
                </div>
            </div>
            <div class="element">
                <label for="grouptitle">{$c->t('Give a nickname to this group if you want')}</label>
                <input type="text" name="title" value="{$groupid}" id="grouptitle"/>
            </div>
        </fieldset>
        <div class="menu">
            <a 
                class="button tiny icon yes black merged left"
                onclick="
                    {$subscribe}
                    this.onclick=null;"
            >{$c->t('Subscribe')}</a><a 
                class="button tiny icon no black merged right" 
                onclick="
                    movim_toggle_display('#groupsubscribe');"
            >{$c->t('Close')}</a>
        </div>
    </form>
</div>

<div class="popup" id="groupunsubscribe">
    <form name="groupunsubscribe">
        <fieldset>
            <legend>{$c->t('Unsubscribe')}</legend>
            <div class="element">
                <label>{$c->t('Are you sure ?')}</label>
            </div>
        </fieldset>
        <div class="menu">
            <a 
                class="button tiny icon yes black merged left"
                onclick="
                    {$unsubscribe}
                    this.onclick=null;"
            >{$c->t('Unsubscribe')}</a><a 
                class="button tiny icon no black merged right" 
                onclick="
                    movim_toggle_display('#groupunsubscribe');"
            >{$c->t('Close')}</a>
        </div>
    </form>
</div>

{$submitform}
{$posts}
