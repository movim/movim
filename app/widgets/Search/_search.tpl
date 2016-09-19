<section id="search">
    <div id="results">{$empty}</div>

    <ul id="roster" class="list">
        {if="$contacts"}
            <li class="subheader"><p>{$c->__('page.contacts')}</p></li>
        {/if}
        {loop="$contacts"}
            <li
                id="{$value->jid|cleanupId}"
                title="{$value->jid}"
                name="{$value->jid|cleanupId}-{$value->getTrueName()|cleanupId}-{$value->groupname|cleanupId}"
                class="{if="$value->value == null"}faded{/if}"

            >
                {$url = $value->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}"
                        style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->jid|stringToColor}
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}"
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
                {if="$value->groupname"}
                <p>
                    <span class="tag color {$value->groupname|stringToColor}">
                        {$value->groupname}
                    </span>
                </p>
                {/if}
            </li>
        {/loop}
    </ul>
</section>
<div>
    <ul class="list">
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-search"></i>
            </span>
            <form name="search" onsubmit="return false;">
                <div>
                    <input name="keyword" placeholder="{$c->__('search.keyword')}" onkeyup="Search_ajaxSearch(this.value); Search.roster(this.value)" type="text">
                </div>
            </form>
        </li>
    </ul>
</div>
