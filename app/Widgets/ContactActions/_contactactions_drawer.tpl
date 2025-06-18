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
                {if="!$contact->isMe()"}
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

    <ul class="list middle">
        <li>
            <div>
                <p class="normal">
                    {if="$contact->fn != null"}
                        {$contact->fn}
                        {if="$contact->nickname != null"}
                         <span class="second">{$contact->nickname}</span>
                        {/if}
                    {elseif="$contact->nickname != null"}
                        {$contact->nickname}
                    {/if}
                </p>
                <p class="all">
                    {if="$contact->description != null && trim($contact->description) != ''"}
                        {autoescape="off"}
                            {$contact->description|trim|nl2br|addEmojis|addUrls|addHashtagsLinks}
                        {/autoescape}
                        <br /><br />
                    {/if}

                    {if="$roster && $roster->presence && $roster->presence->seen"}
                        <i class="material-symbols icon-text">schedule</i>
                        {$c->__('last.title')} {$roster->presence->seen|prepareDate:true,true}
                        <br />
                    {/if}

                    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
                        <i class="material-symbols icon-text">place</i>
                        {if="$contact->adrlocality != null"}
                            {$contact->adrlocality}
                        {/if}
                        {if="$contact->adrcountry != null"}
                            {$contact->adrcountry}
                        {/if}
                        <br />
                    {/if}

                    {if="$contact->date && strtotime($contact->date) != 0"}
                        <i class="material-symbols icon-text">cake</i>
                        {$contact->date|prepareDate:false}
                        <br />
                    {/if}

                    {if="$contact->email"}
                        <i class="material-symbols icon-text">email</i>
                        <a href="mailto:{$contact->email}">{$contact->email}</a>
                        <br />
                    {/if}

                    {if="$contact->phone"}
                        <i class="material-symbols icon-text">phone</i>
                        <a href="tel:{$contact->phone}">{$contact->phone}</a>
                        <br />
                    {/if}

                    {if="$contact->url != null"}
                        <i class="material-symbols icon-text">link</i>
                        {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                            <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                        {else}
                            {$contact->url}
                        {/if}
                        <br />
                    {/if}

                    {if="$contact->locationDistance != null"}
                        <i class="material-symbols icon-text">place</i>
                        <a href="{$contact->locationUrl}" target="_blank">{$contact->locationDistance|humanDistance}</a> - {$contact->loctimestamp|prepareDate:true,true}
                        <br />
                    {/if}
                </p>
            </div>
        </li>
    </ul>

    {if="$posts->count() > 0"}
        <ul class="list card shadow flex active middle">
            {loop="$posts"}
                {autoescape="off"}
                    {$c->prepareTicket($value)}
                {/autoescape}
            {/loop}
        </ul>
    {/if}

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
            </ul>
        </div>
    {/if}

    {if="$hasfingerprints && $c->getUser()->hasOMEMO()"}
        <div class="tabelem spin" title="{$c->__('omemo.fingerprints_title')}" id="omemo_fingerprints"></div>
    {/if}

    <div id="adhoc_widget_{$jid|cleanupId}"
        class="adhoc_widget tabelem"
        title="{$c->__('adhoc.title')}"
        data-mobileicon="terminal" >
        <div class="placeholder">
            <i class="material-symbols">terminal</i>
            <h1>{$c->__('adhoc.title')}</h1>
        </div>
    </div>

    <br />
</section>
