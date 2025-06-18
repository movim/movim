<ul class="list">
    <li class="block large">
        <div>
            <p class="center all">
                <img src="{if="$roster"}{$roster->getPicture(\Movim\ImageSize::XL)}{else}{$contact->getPicture(\Movim\ImageSize::XL)}{/if}" class="avatar">
            </p>
        </div>
    </li>
</ul>

<ul class="list thick">
    <li>
        <div>
            <p class="normal center">
                {$contact->truename}
                {if="$roster && $roster->name && $roster->name != $contact->truename"}
                    <span class="second">{$roster->name}</span>
                    <br />
                {/if}
                {if="$roster && $roster->presence"}
                    <span class="second">{$roster->presence->presencetext}</span>
                {/if}
            </p>
            <p class="all center">
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

                {if="$contact->email"}
                    <i class="material-symbols icon-text">email</i>
                    <a href="mailto:{$contact->email}" rel="me">{$contact->email}</a>
                    <br />
                {/if}

                {if="$contact->phone"}
                    <i class="material-symbols icon-text">phone</i>
                    <a href="tel:{$contact->phone}" rel="me">{$contact->phone}</a>
                    <br />
                {/if}

                {if="$contact->url != null"}
                    <i class="material-symbols icon-text">link</i>
                    {if="parse_url($contact->url, PHP_URL_SCHEME) == 'xmpp'"}

                        <a href="{$contact->url}" onclick="MovimUtils.reload('{$c->route('contact', substr($contact->url, 5))}'); return false" rel="me">{$contact->url}</a>
                    {elseif="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                        <a href="{$contact->url}" target="_blank" rel="me">{$contact->url}</a>
                    {else}
                        {$contact->url}
                    {/if}
                    <br />
                {/if}

                {if="$contact->locationDistance != null && $contact->locationUrl != null"}
                    <i class="material-symbols icon-text">place</i>
                    <a href="{$contact->locationUrl}" target="_blank">{$contact->locationDistance|humanDistance}</a> - {$contact->loctimestamp|prepareDate:true,true}
                    <br />
                {elseif="$contact->hasLocation() && $contact->locationUrl != null"}
                    <i class="material-symbols icon-text">place</i>
                    <a href="{$contact->locationUrl}" target="_blank">{$c->__('location.last_published')}</a> - {$contact->loctimestamp|prepareDate:true,true}
                    <br />
                {/if}
            </p>
        </div>
    </li>
</ul>
