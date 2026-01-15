<section>
    <header class="big color {$contact->color}"
        style="background-image: linear-gradient(to bottom, rgba(23,23,23,0.8) 0%, rgba(23,23,23,0.5) 100%), url('{$contact->getBanner(\Movim\ImageSize::XXL)}');"
        >
        <ul class="list thick">
            <li>
                {if="!$contact->isFromMuc()"}
                    <span onclick="MovimUtils.reload('{$c->route('contact', $jid)}'); Drawer.clear();"
                    class="primary icon bubble active
                        {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}
                    ">
                        <img src="{if="$roster"}{$roster->getPicture()}{else}{$contact->getPicture()}{/if}">
                    </span>
                {/if}
                {if="!$contact->isFromMuc()"}
                    <span class="control icon active" onclick="MovimUtils.reload('{$c->route('contact', $contact->id)}'); Drawer.clear();">
                        <i class="material-symbols">person</i>
                    </span>
                {/if}
                {if="!$contact->isContact($c->me->id)"}
                    <span class="control icon active divided" onclick="Search.chat('{$contact->id|echapJS}', false); Drawer.clear();">
                        <i class="material-symbols">comment</i>
                    </span>
                    {if="$roster && $roster->presences->count() > 0 && !$incall"}
                        {loop="$roster->presences"}
                            {if="$value->capability && $value->capability->isJingleAudio()"}
                                <span title="{$c->__('button.audio_call')}" class="control icon active"
                                    onclick="Visio_ajaxGetLobby('{$value->jid|echapJS}', true); Drawer.clear();">
                                    <i class="material-symbols">phone</i>
                                </span>
                            {/if}
                            {if="$value->capability && $value->capability->isJingleVideo()"}
                                <span title="{$c->__('button.video_call')}" class="control icon active"
                                    onclick="Visio_ajaxGetLobby('{$value->jid|echapJS}', true, true); Drawer.clear();">
                                    <i class="material-symbols">videocam</i>
                                </span>
                                {break}
                            {/if}
                        {/loop}
                    {/if}
                {/if}
                <div>
                    <p class="line">
                        {$contact->truename}
                        {if="$c->me->isBlocked($contact)"}
                            <span class="tag color red">{$c->__('blocked.title')}</span>
                        {/if}
                        {if="$roster && $roster->group"}
                            <span class="tag color {$roster->group|stringToColor}">{$roster->group}</span>
                        {/if}
                    </p>
                    <p class="line">
                        {if="$roster && $roster->name && $roster->name != $contact->truename"}
                            {$roster->name} •
                        {/if}
                        {$contact->id}
                    </p>
                </div>
            </li>
        </ul>
    </header>

    {autoescape="off"}
        {$c->prepareVcard($contact, $roster)}
    {/autoescape}

    <ul class="tabs" id="navtabs"></ul>

    {if="isset($picturesCount) && $picturesCount > 0"}
        <div class="tabelem spin" title="{$c->__('general.pictures')}" id="contact_pictures"></div>
    {/if}

    {if="isset($linksCount) && $linksCount > 0"}
        <div class="tabelem spin" title="{$c->__('general.links')}" id="contact_links"></div>
    {/if}

    {if="$roster && $roster->presences->count() > 0"}
        <div class="tabelem" title="{$c->__('clients.title')}" id="clients">
            <ul class="list middle">
                {if="$roster->presences->isNotEmpty()"}
                    <li class="subheader">
                        <div>
                            <p>{$c->__('clients.title_full')}</p>
                        </div>
                    </li>
                    {loop="$roster->presences"}
                        {if="$value->capability"}
                            <li class="block">
                                <span class="primary icon gray status {$value->presencekey}">
                                    <i class="material-symbols">
                                        {$value->capability->getDeviceIcon()}
                                    </i>
                                </span>
                                <div>
                                    <p class="normal line">
                                        {$value->capability->name}
                                        <span class="second">{$value->resource}</span>
                                    </p>
                                    {if="$value->capability->identities()->first() && isset($clienttype[$value->capability->identities()->first()->type])"}
                                        <p class="line">
                                            {$clienttype[$value->capability->identities()->first()->type]}
                                        </p>
                                    {/if}
                                </div>
                            </li>
                        {/if}
                    {/loop}
                {else}
                    <ul class="thick">
                        <div class="placeholder">
                            <i class="material-symbols">mobile</i>
                            <h1>{$c->__('clients.title_full')}</h1>
                        </li>
                    </ul>
                {/if}
            </ul>
        </div>
    {/if}

    {if="$c->me->hasOMEMO()"}
        <div class="tabelem spin" title="{$c->__('omemo.fingerprints_title')}" id="omemo_fingerprints"></div>
    {/if}

    <div id="adhoc_widget"
        class="tabelem"
        title="{$c->__('adhoc.title')}">
        <ul class="list middle active">
            {if="$c->me->isBlocked($contact)"}
                <li onclick="ContactActions_ajaxUnblock('{$contact->id|echapJS}'); Drawer.clear();">
                    <span class="primary icon green">
                        <i class="material-symbols">cancel</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-symbols">chevron_right</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('blocked.unblock_account')}</p>
                    </div>
                </li>
            {else}
                <li onclick="ContactActions_ajaxBlock('{$contact->id|echapJS}'); Drawer.clear();">
                    <span class="primary icon red">
                        <i class="material-symbols">block</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-symbols">chevron_right</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('blocked.block_account')}</p>
                    </div>
                </li>
            {/if}
            <li onclick="Chat_ajaxClearHistory('{$contact->id|echapJS}')">
                <span class="primary icon gray">
                    <i class="material-symbols">clear_all</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('chat.clear_history')}</p>
                </div>
            </li>
        </ul>
        <div class="adhoc_widget" id="adhoc_widget_{$jid|cleanupId}">
            <div class="placeholder">
                <i class="material-symbols">terminal</i>
                <h1>{$c->__('adhoc.title')}</h1>
            </div>
        </div>
    </div>

    <br />
</section>
