<section>
    {$url = $contact->getPhoto()}
    <header class="big"
        {if="$url"}
            style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%), url('{$contact->getPhoto('xxl')}');"
        {else}
            style="background-color: rgba(62,81,181,1);"
        {/if}
        >
        <ul class="list middle">
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
                    <span class="control icon active" onclick="ContactActions_ajaxChat('{$contact->id}')">
                        <i class="material-icons">comment</i>
                    </span>
                {/if}
                {if="$roster && $roster->presence && $roster->presence->capability && $roster->presence->capability->isJingle()"}
                    <span title="{$c->__('button.call')}" class="control icon active"
                          onclick="VisioLink.openVisio('{$roster->presence->jid . '/' . $roster->presence->resource}');">
                        <i class="material-icons">phone</i>
                    </span>
                {/if}
                <p class="line">{$contact->truename}</p>
                <p class="line">{$contact->id}</p>
            </li>
        </ul>
    </header>

    {if="$roster && $roster->presences"}
        <ul class="list middle">
            <li class="subheader"><p>{$c->__('clients.title')}</p></li>
            {loop="$roster->presences"}
                {if="$value->capability"}
                    <li class="block">
                        <span class="primary icon gray">
                            <i class="material-icons">
                                {$value->capability->getDeviceIcon()}
                            </i>
                        </span>
                        <p class="normal line">
                            {$value->capability->name}
                        </p>
                        <p class="line">
                            {if="isset($clienttype[$value->capability->type])"}
                                {$clienttype[$value->capability->type]}
                            {/if}
                        </p>
                    </li>
                {/if}
            {/loop}
        </ul>
        <hr class="thick"/>
    {/if}

    <ul class="list middle">
        {if="$contact->fn != null"}
        <li>
            <span class="primary icon gray">{$contact->fn|firstLetterCapitalize}</span>
            <p>{$c->__('general.name')}</p>
            <p>{$contact->fn}</p>
        </li>
        {/if}

        {if="$contact->nickname != null"}
        <li>
            <span class="primary icon gray">{$contact->nickname|firstLetterCapitalize}</span>
            <p>{$c->__('general.nickname')}</p>
            <p>{$contact->nickname}</p>
        </li>
        {/if}

        {if="$contact->url != null"}
        <li>
            <span class="primary icon gray"><i class="material-icons">link</i></span>
            <p>{$c->__('general.website')}</p>
            <p>
                {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                    <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                {else}
                    {$contact->url}
                {/if}
            </p>
        </li>
        {/if}

        {if="$contact->email != null"}
        <li>
            <span class="primary icon gray"><i class="material-icons">email</i></span>
            <p>{$c->__('general.email')}</p>
            <p><a href="mailto:{$contact->email}">{$contact->email}</a></p>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li>
            <span class="primary icon gray"><i class="material-icons">subject</i></span>
            <p>{$c->__('general.about')}</p>
            <p class="all">
                {autoescape="off"}
                    {$contact->description|nl2br}
                {/autoescape}
            </p>
        </li>
        {/if}

        {if="strtotime($contact->date) != 0"}
        <li class="block">
            <span class="primary icon gray"><i class="material-icons">cake</i></span>
            <p>{$c->__('general.date_of_birth')}</p>
            <p>{$contact->date|strtotime|prepareDate:false}</p>
        </li>
        {/if}
    </ul>

    <br />
</section>
