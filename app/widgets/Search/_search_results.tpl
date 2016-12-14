{if="$empty == true"}
    <div class="placeholder icon search">
        <h4>{$c->__('search.subtitle')}</h4>
    </div>
{else}
    <ul class="list active">
        {if="$posts"}
            <li class="subheader"><p>{$c->__('page.news')}</p></li>
        {/if}
        {loop="$posts"}
            <li onclick="MovimUtils.reload('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
                {if="$value->title != null"}
                    <p class="line">{$value->title}</p>
                {else}
                    <p class="line">{$c->__('menu.contact_post')}</p>
                {/if}
                <p>
                    {if="$value->isMicroblog()"}
                        <a href="{$c->route('contact', $value->getContact()->jid)}">
                            <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
                        </a>
                    {else}
                        <a href="{$c->route('community', [$value->origin, $value->node])}">
                            <i class="zmdi zmdi-pages"></i> {$value->node}
                        </a>
                    {/if}
                    <span class="info">
                        {$value->published|strtotime|prepareDate:true,true}
                    </span>
                </p>
            </li>
        {/loop}
    </ul>
{/if}
