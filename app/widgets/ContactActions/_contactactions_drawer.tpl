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
                    <span class="control icon active" onclick="ContactActions_ajaxChat('{$contact->id|echapJS}')">
                        <i class="material-icons">comment</i>
                    </span>
                    {if="$roster && $roster->presences->count() > 0"}
                        {loop="$roster->presences"}
                            {if="$value->capability && $value->capability->isJingle()"}
                                <span title="{$c->__('button.call')}" class="control icon active"
                                    onclick="VisioLink.openVisio('{$value->jid}');">
                                    <i class="material-icons">phone</i>
                                </span>
                                {break}
                            {/if}
                        {/loop}
                    {/if}
                {/if}
                <content>
                    <p class="line">{$contact->truename}</p>
                    <p class="line">{$contact->id}</p>
                </content>
            </li>
        </ul>
    </header>

    {if="$roster && $roster->presences->count() > 0"}
        <ul class="list middle">
            <li class="subheader">
                <content>
                    <p>{$c->__('clients.title')}</p>
                </content>
            </li>
            {loop="$roster->presences"}
                {if="$value->capability"}
                    <li class="block">
                        <span class="primary icon gray status {$value->presencekey}">
                            <i class="material-icons">
                                {$value->capability->getDeviceIcon()}
                            </i>
                        </span>
                        <content>
                            <p class="normal line">
                                {$value->capability->name}
                                <span class="second">{$value->resource}</span>
                            </p>
                            {if="$value->capability->identities()->first() && isset($clienttype[$value->capability->identities()->first()->type])"}
                                <p class="line">
                                    {$clienttype[$value->capability->identities()->first()->type]}
                                </p>
                            {/if}
                        </content>
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
            <content>
                <p>{$c->__('general.name')}</p>
                <p>{$contact->fn}</p>
            </content>
        </li>
        {/if}

        {if="$contact->nickname != null"}
        <li>
            <span class="primary icon gray">{$contact->nickname|firstLetterCapitalize}</span>
            <content>
                <p>{$c->__('general.nickname')}</p>
                <p>{$contact->nickname}</p>
            </content>
        </li>
        {/if}

        {if="$contact->url != null"}
        <li>
            <span class="primary icon gray"><i class="material-icons">link</i></span>
            <content>
                <p>{$c->__('general.website')}</p>
                <p>
                    {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                        <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                    {else}
                        {$contact->url}
                    {/if}
                </p>
            </content>
        </li>
        {/if}

        {if="$contact->email != null"}
        <li>
            <span class="primary icon gray"><i class="material-icons">email</i></span>
            <content>
                <p>{$c->__('general.email')}</p>
                <p><a href="mailto:{$contact->email}">{$contact->email}</a></p>
            </content>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li>
            <span class="primary icon gray"><i class="material-icons">subject</i></span>
            <content>
                <p>{$c->__('general.about')}</p>
                <p class="all">
                    {autoescape="off"}
                        {$contact->description|nl2br}
                    {/autoescape}
                </p>
            </content>
        </li>
        {/if}

        {if="strtotime($contact->date) != 0"}
        <li class="block">
            <span class="primary icon gray"><i class="material-icons">cake</i></span>
            <content>
                <p>{$c->__('general.date_of_birth')}</p>
                <p>{$contact->date|strtotime|prepareDate:false}</p>
            </content>
        </li>
        {/if}
    </ul>

    <br />
</section>
