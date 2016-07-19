<section>
    {$url = $contact->getPhoto('s')}
    <header class="big"
        {if="$url"}
            style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 100%), url('{$contact->getPhoto('xxl')}');"
        {else}
            style="background-color: rgba(62,81,181,1);"
        {/if}
        >
        <ul class="list thick">
            <li>
                {if="$url"}
                    <span class="primary icon bubble color {if="isset($presence)"}status {$presence}{/if}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$contact->jid|stringToColor} {if="isset($presence)"}status {$presence}{/if}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                <span class="control icon active" onclick="Contact_ajaxChat('{$contact->jid}');">
                    <i class="zmdi zmdi-comment-text-alt"></i>
                </span>
                <span class="control icon active" onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
                    <i class="zmdi zmdi-account"></i>
                </span>
                <p>{$contact->getTrueName()}</p>
                <p>{$contact->jid}</p>
            </li>
        </ul>
    </header>

    <ul class="list middle">
        {if="$caps"}
            <li class="block">
                <span class="primary icon gray">
                    <i class="zmdi
                        {if="$caps->type == 'handheld' || $caps->type == 'phone'"}
                            zmdi-smartphone-android
                        {elseif="$caps->type == 'bot'"}
                            zmdi-memory
                        {elseif="$caps->type == 'web'"}
                            zmdi-globe-alt
                        {else}
                            zmdi-laptop
                        {/if}
                    ">
                    </i>
                </span>
                <p class="normal line">
                    {$caps->name}
                </p>
                <p class="line">
                    {if="isset($clienttype[$caps->type])"}
                        {$clienttype[$caps->type]}
                    {/if}
                </p>
            </li>
        {/if}

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
        <li>
            <span class="primary icon gray"><i class="zmdi zmdi-email"></i></span>
            <p>{$c->__('general.email')}</p>
            <p><img src="{$contact->getPhoto('email')}"/></p>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li>
            <span class="primary icon gray"><i class="zmdi zmdi-format-align-justify"></i></span>
            <p>{$c->__('general.about')}</p>
            <p class="all">{$contact->description}</p>
        </li>
        {/if}
    </ul>
</section>
