<div class="lists">
    <ul class="list card shadow flex gallery active" style="{if="$stories->count()"}grid-template-columns: 0.5fr repeat({$stories->count()}, 1fr);{/if}">
        <li class="block story add" onclick="PublishStories_ajaxOpen()">
            <div>
                <p><i class="material-symbols">add_circle</i></p>
            </div>
        </li>
        {loop="$stories"}
            <li class="block story {if="$value->my_views_count > 0"}seen{/if}" onclick="StoriesViewer_ajaxHttpGet({$value->id})">
                <img class="main" src="{$value->picture->href|protectPicture}">
                <div>
                    <p class="line">{$value->title}</p>
                    <p class="line">

                        {if="$value->contact"}
                            <span class="icon bubble tiny">
                                <img src="{$value->contact->getPicture()}">
                            </span>
                        {/if}
                        <a href="#" onclick="MovimUtils.reload('{$c->route('contact', $value->aid)}')">
                            {$value->truename}
                        </a>
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
    {if="$topcontacts->isNotEmpty()"}
        <ul class="list card shadow flex compact middle active" style="grid-template-columns: repeat({$topcontacts->count()}, 1fr); min-width: {$topcontacts->count() * 15}rem">
            {loop="$topcontacts"}
                <li class="block {if="$value->last > 60"} inactive{/if}"
                    onclick="Stories_ajaxOpenChat('{$value->jid|echapJS}');">
                    <img class="main" src="{$value->getBanner(\Movim\ImageSize::L)}">
                    <span class="primary icon bubble
                        {if="$value->presence"}
                            status {$value->presence->presencekey}
                        {/if}">
                        <img src="{$value->getPicture()}">
                    </span>
                    <div>
                        <p class="line" title="{$value->truename}">
                            {$value->truename}

                            {if="$value->presence && $value->presence->capability"}
                                <span class="second" title="{$value->presence->capability->name}">
                                    <i class="material-symbols">{$value->presence->capability->getDeviceIcon()}</i>
                                </span>
                            {/if}
                        </p>

                        {if="$value->presence && $value->presence->seen"}
                            <p class="line" title="{$c->__('last.title')} {$value->presence->seen|prepareDate:true,true}">
                                {$c->__('last.title')} {$value->presence->seen|prepareDate:true,true}
                            </p>
                        {elseif="$value->presence"}
                            <p class="line">{$value->presence->presencetext}</p>
                        {else}
                            <p></p>
                        {/if}
                    </div>
                </li>
            {/loop}
        </ul>
    {/if}
</div>
