<section>
    {$url = $contact->getPhoto()}
    <header class="big"
        {if="$url"}
            style="background-image: linear-gradient(to bottom, rgba(23,23,23,0.8) 0%, rgba(23,23,23,0.5) 100%), url('{$contact->getPhoto('xxl')}');"
        {/if}
        >
        <ul class="list thick">
            <li>
                {if="$url"}
                    <span onclick="MovimUtils.reload('{$c->route('contact', $jid)}')"
                    class="primary icon bubble color active
                        {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}
                    ">
                        <img src="{$url}">
                    </span>
                {elseif="!$contact->isFromMuc()"}
                    <span  onclick="MovimUtils.reload('{$c->route('contact', $jid)}')"
                    class="primary icon bubble color {$contact->id|stringToColor} active
                        {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}
                    ">
                        <i class="material-icons">person</i>
                    </span>
                {/if}
                {if="!$contact->isFromMuc()"}
                    <span class="control icon active" onclick="MovimUtils.reload('{$c->route('contact', $contact->id)}')">
                        <i class="material-icons">person</i>
                    </span>
                {/if}
                {if="!$contact->isMe()"}
                    <span class="control icon active divided" onclick="ContactActions_ajaxChat('{$contact->id|echapJS}')">
                        <i class="material-icons">comment</i>
                    </span>
                    {if="$roster && $roster->presences->count() > 0"}
                        {loop="$roster->presences"}
                            {if="$value->capability && $value->capability->isJingleAudio()"}
                                <span title="{$c->__('button.audio_call')}" class="control icon active"
                                    onclick="VisioLink.openVisio('{$value->jid|echapJS}');">
                                    <i class="material-icons">phone</i>
                                </span>
                            {/if}
                            {if="$value->capability && $value->capability->isJingleVideo()"}
                                <span title="{$c->__('button.video_call')}" class="control icon active"
                                    onclick="VisioLink.openVisio('{$value->jid|echapJS}', '', true);">
                                    <i class="material-icons">videocam</i>
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
                    <p class="line">{$contact->id}</p>
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
                            {$contact->description|trim|nl2br|addEmojis}
                        {/autoescape}
                        <br />
                    {/if}

                    {if="$roster && $roster->presence && $roster->presence->seen"}
                        <br />
                        <i class="material-icons icon-text">schedule</i>
                        {$c->__('last.title')} {$roster->presence->seen|strtotime|prepareDate:true,true}
                    {/if}

                    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
                        <br />
                        <i class="material-icons icon-text">place</i>
                        {if="$contact->adrlocality != null"}
                            {$contact->adrlocality}
                        {/if}
                        {if="$contact->adrcountry != null"}
                            {$contact->adrcountry}
                        {/if}
                    {/if}

                    {if="strtotime($contact->date) != 0"}
                        <br />
                        <i class="material-icons icon-text">cake</i>
                        {$contact->date|strtotime|prepareDate:false}
                    {/if}

                    {if="$contact->email"}
                        <br />
                        <i class="material-icons icon-text">email</i>
                        <a href="mailto:{$contact->email}">{$contact->email}</a>
                    {/if}

                    {if="$contact->url != null"}
                        <br />
                        <i class="material-icons icon-text">link</i>
                        {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                            <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                        {else}
                            {$contact->url}
                        {/if}
                    {/if}
                </p>
            </div>
        </li>
    </ul>

    <ul class="tabs" id="navtabs"></ul>

    {if="$pictures->count() > 0"}
        <div class="tabelem" title="{$c->__('general.pictures')}" id="contact_medias">
            <ul class="grid active">
                {loop="$pictures"}
                    <li style="background-image: url('{$value->file['uri']|protectPicture}')"
                        onclick="Preview_ajaxHttpShow('{$value->file['uri']}')">
                        <i class="material-icons">visibility</i>
                    </li>
                {/loop}
            </ul>
        </div>
    {/if}

    {if="$links->count() > 0"}
        <div class="tabelem" title="{$c->__('general.links')}" id="room_links">
            <ul class="active list thick">
                {loop="$links"}
                    {autoescape="off"}
                        {$resolved = $value->resolvedUrl->cache}
                        {if="$resolved"}
                            {$c->prepareEmbedUrl($resolved)}
                        {/if}
                    {/autoescape}
                {/loop}
            </ul>
        </div>
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
                                <i class="material-icons">
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

    {if="$hasfingerprints"}
        <div class="tabelem spin" title="{$c->__('omemo.fingerprints_title')}" id="omemo_fingerprints"></div>
    {/if}

    <br />
</section>
