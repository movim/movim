<div id="menu_refresh"></div>
{if="$items"}
    <ul class="thick active divided">
        {loop="$items"}
            <li class="condensed" data-id="{$value->nodeid}">
                {if="current(explode('.', $value->jid)) == 'nsfw'"}
                    <span class="icon bubble color red">
                        <i class="md md-warning"></i>
                    </span>
                {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                    <span class="icon bubble color {$value->jid|stringToColor}">
                        <i class="md md-create"></i>
                    </span>                    
                {else}
                    <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                {/if}

                {if="$value->title != null"}
                    <span>{$value->title}</span>
                {else}
                    <span>{$c->__('menu.contact_post')}</span>
                {/if}

                <span class="info">{$value->published|strtotime|prepareDate}</span>
                
                {if="$value->node == 'urn:xmpp:microblog:0'"}
                    <p class="wrap">{$value->jid}</p>
                {else}
                    <p class="wrap">{$value->node}</p>
                {/if}
            </li>
        {/loop}

        <li onclick="{$history} this.parentNode.removeChild(this);">
            <span class="icon"><i class="md md-history"></i></span>
            {$c->__('post.older')}
        </li>
    </ul>
{elseif="$page == 0"}
    <br/>
    <ul>
        <li class="condensed">
            <span class="icon bubble color orange">
                <i class="md md-format-align-left"></i>
            </span>
            <span>{$c->__('menu.empty_title')}</span>
            <p>{$c->__('menu.empty')}</p>
        </li>
    </ul>
{/if}
