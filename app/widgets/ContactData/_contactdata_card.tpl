{$url = $contact->getPhoto('l')}
{if="$url"}
<ul class="list middle">
    <li>
        <div>
            <p class="center">
                <img class="avatar" src="{$url}">
            </p>
        </div>
    </li>
</ul>
{/if}

<div class="block">
    <ul class="list middle">
        <li>
            <div>
                <p class="normal center	">
                    {$contact->truename}
                    {if="isset($roster) && isset($roster->presence)"}
                        <span class="second">{$roster->presence->presencetext}</span>
                    {/if}
                </p>

                {if="$roster && $roster->presence && $roster->presence->seen"}
                    <p class="center">
                        <span class="second">
                            {$c->__('last.title')} {$roster->presence->seen|strtotime|prepareDate:true,true}
                        </span>
                    </p>
                {/if}
                {if="$contact->email"}
                    <p class="center"><a href="mailto:{$contact->email}">{$contact->email}</a></p>
                {/if}
                {if="$contact->description != null && trim($contact->description) != ''"}
                    <p class="center all" title="{$contact->description}">
                        {autoescape="off"}
                            {$contact->description|nl2br|addEmojis}
                        {/autoescape}
                    </p>
                {/if}
            </div>
        </li>
        <!--<li>
            <span class="primary icon gray">
                <i class="material-icons">accounts</i>
            </span>
            <p class="normal">{$c->__('communitydata.sub', 0)}</p>
        </li>-->
    </ul>

    {if="$contact->url != null"}
        <ul class="list thin">
            <li>
                <span class="primary icon gray"><i class="material-icons">link</i></span>
                <div>
                    <p class="normal line">
                        {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                            <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                        {else}
                            {$contact->url}
                        {/if}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray"><i class="material-icons">location_city</i></span>
                <div>
                    {if="$contact->adrlocality != null"}
                        <p class="normal">{$contact->adrlocality}</p>
                    {/if}
                    {if="$contact->adrcountry != null"}
                        <p {if="$contact->adrlocality == null"}class="normal"{/if}>
                            {$contact->adrcountry}
                        </p>
                    {/if}
                </div>
            </li>
        </ul>
    {/if}
</div>