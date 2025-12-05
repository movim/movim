<ul class="list thick">
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
                {else}
                    {$contact->id}
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

                {if="$contact->pronouns"}
                    <i class="material-symbols icon-text">id_card</i>
                    {$contact->pronouns}
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
