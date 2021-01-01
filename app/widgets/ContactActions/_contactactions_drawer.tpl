<section>
    {$url = $contact->getPhoto()}
    <header class="big"
        {if="$url"}
            style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.5) 100%), url('{$contact->getPhoto('xxl')}');"
        {/if}
        >
        <ul class="list thick">
            <li>
                {if="$url"}
                    <span class="primary icon bubble color
                        {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}
                    ">
                        <img src="{$url}">
                    </span>
                {elseif="!$contact->isFromMuc()"}
                    <span class="primary icon bubble color {$contact->id|stringToColor}
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
                    <p class="line">{$contact->truename}</p>
                    <p class="line">{$contact->id}</p>
                </div>
            </li>
        </ul>
    </header>

    {if="$roster && $roster->presences->count() > 0"}
        <ul class="list middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('clients.title')}</p>
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
    {/if}

    {if="$pictures->count() > 0"}
        <ul class="list">
            <li class="subheader">
                <div>
                    <p>
                        {$c->__('general.pictures')}
                    </p>
                </div>
            </li>
        </ul>
        <ul class="grid active">
            {loop="$pictures"}
                <li style="background-image: url('{$value->file['uri']|protectPicture}')"
                    onclick="Preview_ajaxShow('{$value->file['uri']}')">
                    <i class="material-icons">visibility</i>
                </li>
            {/loop}
        </ul>
    {/if}

    <ul class="list middle">
        <li class="subheader">
            <div>
                <p>
                    {$c->__('vcard.title')}
                </p>
            </div>
        </li>

        {if="$roster && $roster->presence && $roster->presence->seen"}
        <li>
            <span class="primary icon gray">
                <i class="material-icons">access_time</i>
            </span>
            <div>
                <p>{$c->__('last.title')}</p>
                <p>
                    {$roster->presence->seen|strtotime|prepareDate:true,true}
                </p>
            </div>
        </li>
        {/if}

        {if="$contact->fn != null"}
        <li>
            <span class="primary icon gray">{$contact->fn|firstLetterCapitalize}</span>
            <div>
                <p>{$c->__('general.name')}</p>
                <p>{$contact->fn}</p>
            </div>
        </li>
        {/if}

        {if="$contact->nickname != null"}
        <li>
            <span class="primary icon gray">{$contact->nickname|firstLetterCapitalize}</span>
            <div>
                <p>{$c->__('general.nickname')}</p>
                <p>{$contact->nickname}</p>
            </div>
        </li>
        {/if}

        {if="$roster->group"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">recent_actors</i>
                </span>
                <div>
                    <p>{$c->__('edit.group')}</p>
                    <p>
                        <span class="tag color {$roster->group|stringToColor}">
                            {$roster->group}
                        </span>
                    </p>
                </div>
            </li>
        {/if}

        {if="$contact->url != null"}
        <li>
            <span class="primary icon gray">
                <i class="material-icons">link</i>
            </span>
            <div>
                <p>{$c->__('general.website')}</p>
                <p>
                    {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                        <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                    {else}
                        {$contact->url}
                    {/if}
                </p>
            </div>
        </li>
        {/if}

        {if="$contact->email != null"}
        <li>
            <span class="primary icon gray"><i class="material-icons">email</i></span>
            <div>
                <p>{$c->__('general.email')}</p>
                <p><a href="mailto:{$contact->email}">{$contact->email}</a></p>
            </div>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li>
            <span class="primary icon gray"><i class="material-icons">subject</i></span>
            <div>
                <p>{$c->__('general.about')}</p>
                <p class="all">
                    {autoescape="off"}
                        {$contact->description|nl2br}
                    {/autoescape}
                </p>
            </div>
        </li>
        {/if}

        {if="strtotime($contact->date) != 0"}
        <li class="block">
            <span class="primary icon gray"><i class="material-icons">cake</i></span>
            <div>
                <p>{$c->__('general.date_of_birth')}</p>
                <p>{$contact->date|strtotime|prepareDate:false}</p>
            </div>
        </li>
        {/if}
    </ul>

    <br />
</section>
