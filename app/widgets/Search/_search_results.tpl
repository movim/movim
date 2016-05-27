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
            <li onclick="movim_reload('{$c->route('news', $value->nodeid)}')">
                {if="$value->title != null"}
                    <p class="line">{$value->title}</p>
                {else}
                    <p class="line">{$c->__('menu.contact_post')}</p>
                {/if}
                <p>
                    {if="$value->node == 'urn:xmpp:microblog:0'"}
                        <a href="{$c->route('contact', $value->getContact()->jid)}">
                            <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
                        </a>
                    {else}
                        <a href="{$c->route('group', array($value->origin, $value->node))}">
                            <i class="zmdi zmdi-pages"></i> {$value->node}
                        </a>
                    {/if}
                    <span class="info">
                        {$value->published|strtotime|prepareDate:true,true}
                    </span>
                </p>
            </li>
        {/loop}

        {if="$contacts"}
            <li class="subheader"><p>{$c->__('page.contacts')}</p></li>
        {/if}
        {loop="$contacts"}
            <li>
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->jid|stringToColor}
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                <span class="control icon active gray" onclick="movim_reload('{$c->route('contact', $value->jid)}')">
                    <i class="zmdi zmdi-account"></i>
                </span>
                <span class="control icon active gray" onclick="Search_ajaxChat('{$value->jid}')">
                    <i class="zmdi zmdi-comment-text-alt"></i>
                </span>
                <p class="line">{$value->getTrueName()}</p>
                <p class="line">{$value->jid}</p>
            </li>
        {/loop}
    </ul>
{/if}
