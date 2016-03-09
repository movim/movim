{if="$contact != null"}
    {$url = $contact->getPhoto('s')}

<header class="big"
    {if="$url"}
        style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 100%), url('{$contact->getPhoto('xxl')}');"
    {else}
        style="background-color: rgba(62,81,181,1);"
    {/if}
    >
    <ul class="list middle">
        <li>
            <span class="primary icon active" onclick="MovimTpl.hidePanel(); Contact_ajaxClear({$page});">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            {if="$contactr != null"}
                <span class="control icon active" onclick="{$edit}">
                    <i class="zmdi zmdi-edit"></i>
                </span>
                <span class="control icon active" onclick="{$delete}">
                    <i class="zmdi zmdi-delete"></i>
                </span>

                <p class="line">{$contactr->getTrueName()}</p>
            {else}
                <span class="control icon active" onclick="Roster_ajaxDisplaySearch('{$contact->jid}')">
                    <i class="zmdi zmdi-account-add"></i>
                </span>
                {if="$contact != null"}
                    <p class="line">{$contact->getTrueName()}</p>
                {else}

                    <p class="line">{$jid}</p>
                {/if}
            {/if}
        </li>
    </ul>

    <ul class="list thick flex">
        <li class="block">
            {if="$url"}
                <span class="primary icon bubble color {if="isset($presence)"}status {$presence}{/if}">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$contact->jid|stringToColor} {if="isset($presence)"}status {$presence}{/if}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <p class="line">{$contact->getTrueName()}</p>
            <p class="line">{$contact->jid}</p>
        </li>
        {if="$caps"}
            <li class="block">
                <span class="primary icon">
                    <i class="zmdi
                        {if="$caps->type == 'handheld' || $caps->type == 'phone'"}
                            zmdi-smartphone-android
                        {elseif="$caps->type == 'bot'"}
                            zmdi-memory
                        {else}
                            zmdi-laptop
                        {/if}
                    ">
                    </i>
                </span>
                <p class="normal line">
                    {$caps->name}
                    {if="isset($clienttype[$caps->type])"}
                        - {$clienttype[$caps->type]}
                    {/if}
                </p>
            </li>
        {/if}
    </ul>
</header>

<div class="block">
    <a onclick="{$chat}" class="button action color red">
        <i class="zmdi zmdi-comment-text-alt"></i>
    </a>

    <ul class="list flex">
        {if="$contact->delay != null"}
        <li class="block">
            <span class="icon brown"><i class="zmdi zmdi-restore"></i></span>
            <p>{$c->__('last.title')}</p>
            <p>{$contact->delay}</p>
        </li>
        {/if}

        {if="$contact->fn != null"}
        <li class="block">
            <span class="primary icon gray">{$contact->fn|firstLetterCapitalize}</span>
            <p>{$c->__('general.name')}</p>
            <p>{$contact->fn}</p>
        </li>
        {/if}

        {if="$contact->nickname != null"}
        <li class="block">
            <span class="primary icon gray">{$contact->nickname|firstLetterCapitalize}</span>
            <p>{$c->__('general.nickname')}</p>
            <p>{$contact->nickname}</p>
        </li>
        {/if}

        {if="strtotime($contact->date) != 0"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-cake"></i></span>
            <p>{$c->__('general.date_of_birth')}</p>
            <p>{$contact->date|strtotime|prepareDate:false}</p>
        </li>
        {/if}

        {if="$contact->url != null"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-link"></i></span>
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
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-email"></i></span>
            <p>{$c->__('general.email')}</p>
            <p><img src="{$contact->getPhoto('email')}"/></p>
        </li>
        {/if}

        {if="$contact->getMarital() != null"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-accounts"></i></span>
            <p>{$c->__('general.marital')}</p>
            <p>{$contact->getMarital()}</p>
        </li>
        {/if}

        {if="$contact->getGender() != null"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-face"></i></span>
            <p>{$c->__('general.gender')}</p>
            <p>{$contact->getGender()}</p>
        </li>
        {/if}

        {if="$contactr->delay != null"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-time-countdown"></i></span>
            <p>{$c->__('last.title')}</p>
            <p>{$contactr->delay|strtotime|prepareDate}</p>
        </li>
        {/if}

        {if="$contact->mood != null"}
        {$moods = unserialize($contact->mood)}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-mood"></i></span>
            <p>{$c->__('mood.title')}</p>
            <p>{loop="$moods"}
                {$mood[$value]}
                {/loop}
            </p>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li class="pblock large">
            <span class="primary icon gray"><i class="zmdi zmdi-format-align-justify"></i></span>
            <p>{$c->__('general.about')}</p>
            <p class="all">{$contact->description}</p>
        </li>
        {/if}
    </ul>
    <br />

    {if="$blog != null"}
        <ul class="list middle active block flex">
            <li class="block large subheader">
                <p>{$c->__('blog.last')}</p>
            </li>
            {loop="$blog"}
                <li class="block" onclick="movim_reload('{$c->route('news', $value->nodeid)}')">
                    {$url = $value->getContact()->getPhoto('s')}
                    {if="$url"}
                        <span class="primary icon bubble" style="background-image: url({$url});">
                        </span>
                    {else}
                        <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                            <i class="zmdi zmdi-account"></i>
                        </span>
                    {/if}
                    {if="$value->title != null"}
                        <p class="line">{$value->title}</p>
                    {else}
                        <p class="line">{$c->__('hello.contact_post')}</p>
                    {/if}
                    <p>{$value->published|strtotime|prepareDate}</p>
                    <p>{$value->contentcleaned|strip_tags}</p>
                </li>
            {/loop}
            <a href="{$c->route('blog', array($contact->jid))}" target="_blank" class="block large">
                <li>
                    <span class="primary icon">
                        <i class="zmdi zmdi-portable-wifi"></i>
                    </span>
                    <span class="control icon">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </span>
                    <p class="normal">{$c->__('blog.visit')}</p>
                </li>
            </a>
        </ul>
    {/if}

    {$album = $contact->getAlbum()}
    {if="$album"}
    <ul class="list flex">
        <li class="subheader block large">
            <p>{$c->__('general.tune')}</p>
        </li>

        <li class="
            block
            action
            ">

            <span class="primary icon bubble">
                {if="isset($album->url)"}
                    <img src="{$album->url}"/>
                {else}
                    <i class="zmdi zmdi-play-circle-fill"></i>
                {/if}
            </span>
            <span class="control icon">
                <a href="{$album->url}" target="_blank">
                    <i class="zmdi zmdi-radio"></i>
                </a>
            </span>
            <p>
                {if="$contact->tuneartist"}
                    {$contact->tuneartist} -
                {/if}
                {if="$contact->tunesource"}
                    {$contact->tunesource}
                {/if}
            </p>
            {if="$contact->tunetitle"}
                <p>{$contact->tunetitle}</p>
            {/if}
        </li>
    </ul>
    <br />
    {/if}

    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
    <ul class="list flex">
        <li class="subheader block large">
            <p>{$c->__('position.legend')}</p>
        </li>

        {if="$contact->adrlocality != null"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-city"></i></span>
            <p>{$c->__('position.locality')}</p>
            <p>{$contact->adrlocality}</p>
        </li>
        {/if}
        {if="$contact->adrcountry != null"}
        <li class="block">
            <span class="primary icon gray"><i class="zmdi zmdi-pin"></i></span>
            <p>{$c->__('position.country')}</p>
            <p>{$contact->adrcountry}</p>
        </li>
        {/if}
    </ul>
    <br />
    {/if}

    <div class="clear"></div>
    {if="$contact->twitter != null || $contact->skype != null || $contact->yahoo != null"}
    <ul class="list flex">
        <li class="subheader block large">
            <p>{$c->__('general.accounts')}</p>
        </li>

        {if="$contact->twitter != null"}
        <li class="block">
            <span class="primary icon gray">
                <i class="zmdi zmdi-twitter"></i>
            </span>
            <p>Twitter</p>
            <p>
                <a
                    target="_blank"
                    href="https://twitter.com/{$contact->twitter}">
                    @{$contact->twitter}
                </a>
            </p>
        </li>
        {/if}
        {if="$contact->skype != null"}
        <li class="block">
            <span class="primary icon gray">S</span>
            <p>Skype</p>
            <p>
                <a
                    target="_blank"
                    href="callto://{$contact->skype}">
                    {$contact->skype}
                </a>
            </p>
        </li>
        {/if}
        {if="$contact->yahoo != null"}
        <li class="block">
            <span class="primary icon gray">Y</span>
            <p>Yahoo!</p>
            <p>
                <a
                    target="_blank"
                    href="ymsgr:sendIM?{$contact->yahoo}">
                    {$contact->yahoo}
                </a>
            </p>
        </li>
        {/if}
    </ul>
    <br />
    {/if}

    {if="isset($gallery)"}
        <ul class="list">
            <li class="subheader">
                <p>{$c->__('page.gallery')}</p>
            </li>
        </ul>
        <ul class="grid active padded">
            {loop="$gallery"}
                {$attachements = $value->getAttachements()}
                <li style="background-image: url('{$attachements['pictures'][0]['href']}');"
                    onclick="movim_reload('{$c->route('news', $value->nodeid)}')">
                    <nav>
                        <span>{$value->title}</span>
                    </nav>
                </li>
            {/loop}
        </ul>
    {/if}

    {if="$contactr && $contactr->rostersubscription != 'both'"}
        <div class="card">
            <ul class="list middle">
                <li>
                    {if="$contactr->rostersubscription == 'to'"}
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-arrow-in"></i>
                        </span>
                        <p>{$c->__('subscription.to')}</p>
                        <p>{$c->__('subscription.to_text')}</p>
                        <p>
                            <a class="button flat" onclick="Notifs_ajaxAccept('{$contactr->jid}')">
                                {$c->__('subscription.to_button')}
                            </a>
                        </p>
                    {/if}
                    {if="$contactr->rostersubscription == 'from'"}
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-arrow-out"></i>
                        </span>
                        <p>{$c->__('subscription.from')}</p>
                        <p>{$c->__('subscription.from_text')}</p>
                        <p>
                            <a class="button flat" onclick="Notifs_ajaxAsk('{$contactr->jid}')">
                                {$c->__('subscription.from_button')}
                            </a>
                        </p>
                    {/if}
                    {if="$contactr->rostersubscription == 'none'"}
                        <span class="primary icon gray">
                            <i class="zmdi zmdi-block"></i>
                        </span>

                        <p>{$c->__('subscription.nil')}</p>
                        <p>{$c->__('subscription.nil_text')}</p>
                        <p>
                            <a class="button flat" onclick="Notifs_ajaxAsk('{$contactr->jid}')">
                                {$c->__('subscription.nil_button')}
                            </a>
                        </p>
                    {/if}
                </li>
            </ul>
        </div>
    {/if}
{else}
    <ul class="thick">
        <li>
            <span class="icon bubble"><img src="{$contactr->getPhoto('l')}"></span>
            <h2>{$contactr->getTrueName()}</h2>
        </li>
    </ul>
</div>
{/if}
