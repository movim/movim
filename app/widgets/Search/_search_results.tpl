{if="$empty == true && $contacts == null"}
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

    {if="$contacts != null"}
    <ul class="list">
        <li class="subheader">
            <p>{$c->__('explore.explore')}</p>
        </li>
        {loop="$contacts"}
            <li
                id="{$value->jid|cleanupId}"
                title="{$value->jid}"
            >
                {$url = $value->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble"
                        style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->jid|stringToColor}
                    ">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                <span class="control icon active gray" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
                    <i class="zmdi zmdi-account"></i>
                </span>
                <span class="control icon active gray" onclick="Search_ajaxChat('{$value->jid}')">
                    <i class="zmdi zmdi-comment-text-alt"></i>
                </span>
                <p class="normal line">{$value->getTrueName()}</p>
                {if="$value->isEmpty()"}
                    <p>{$value->jid}</p>
                {/if}
            </li>
        {/loop}
        </ul>
    {/if}
{/if}
