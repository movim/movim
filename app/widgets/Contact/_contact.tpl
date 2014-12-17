{if="$contact != null"}
    <ul class="thick">
        <li>
            <span class="icon bubble"><img src="{$contact->getPhoto('l')}"></span>
            <h2>{$contact->getTrueName()}</h2>
        </li>
    </ul>
    <ul>
        {if="$contact->delay != null"}
        <li class="condensed block">
            <span class="icon brown"><i class="md md-restore"></i></span>
            <span>{$c->__('last.title')}</span>
            <p>{$contact->delay}</p>
        </li>
        {/if}

        {if="$contact->fn != null"}
        <li class="condensed block">
            <span class="icon gray">{$contact->fn|firstLetterCapitalize}</span>
            <span>{$c->__('general.name')}</span>
            <p>{$contact->fn}</p>
        </li>
        {/if}
        
        {if="$contact->nickname != null"}
        <li class="condensed block">
            <span class="icon gray">{$contact->nickname|firstLetterCapitalize}</span>
            <span>{$c->__('general.nickname')}</span>
            <p>{$contact->nickname}</p>
        </li>
        {/if}
        
        {if="strtotime($contact->date) != 0"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-cake"></i></span>
            <span>{$c->__('general.date_of_birth')}</span>
            <p>{$contact->date|strtotime|prepareDate:false}</p>
        </li>
        {/if}

        {if="$contact->url != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-link"></i></span>
            <span>{$c->__('general.website')}</span>
            <p class="wrap"><a href="{$contact->url}" target="_blank">{$contact->url}</a></p>
        </li>
        {/if}

        {if="$contact->email != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-mail"></i></span>
            <span>{$c->__('general.email')}</span>
            <p><img src="{$contact->getPhoto('email')}"/></p>
        </li>
        {/if}

        {if="$contact->getMarital() != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-people"></i></span>
            <span>{$c->__('general.marital')}</span>
            <p>{$contact->getMarital()}</p>
        </li>
        {/if}

        {if="$contact->getGender() != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-face-unlock"></i></span>
            <span>{$c->__('general.gender')}</span>
            <p>{$contact->getGender()}</p>
        </li>
        {/if}

        {if="$contactr->delay != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-av-timer"></i></span>
            <span>{$c->__('last.title')}</span>
            <p>{$contactr->delay|strtotime|prepareDate}</p>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-format-align-justify"></i></span>
            <span>{$c->__('general.about')}</span>
            <p>{$contact->description}</p>
        </li>
        {/if}

        {if="$contact->mood != null"}
        {$moods = unserialize($contact->mood)}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-mood"></i></span>
            <span>{$c->__('mood.title')}</span>
            <p>{loop="$moods"}
                {$mood[$value]}
                {/loop}
            </p>
        </li>
        {/if}
    </ul>

    <div class="clear"></div>
    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
    <ul>
        <li class="subheader"></li>
        <li class="subheader">{$c->__('position.legend')}</li>

        {if="$contact->adrlocality != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-location-city"></i></span>
            <span>{$c->__('position.locality')}</span>
            <p>
                {$contact->adrlocality}
            </p>
        </li>
        {/if}
        {if="$contact->adrcountry != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-place"></i></span>
            <span>{$c->__('position.country')}</span>
            <p>
                {$contact->adrcountry}
            </p>
        </li>
        {/if}
    </ul>
    {/if}

    <div class="clear"></div>
    {if="$contact->twitter != null || $contact->skype != null || $contact->yahoo != null"}
    <ul class="thick">
        <li class="subheader">{$c->__('general.accounts')}</li>

        {if="$contact->twitter != null"}
        <li class="condensed block">
            <span class="icon gray">T</span>
            <span>Twitter</span>
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
        <li class="condensed block">
            <span class="icon gray">S</span>
            <span>Skype</span>
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
        <li class="condensed block">
            <span class="icon gray">Y</span>
            <span>Yahoo!</span>
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
    {/if}
{else}
    <ul class="thick">
        <li>
            <span class="icon bubble"><img src="{$contactr->getPhoto('l')}"></span>
            <h2>{$contactr->getTrueName()}</h2>
        </li>
    </ul>
{/if}

<a class="button action color red">
    <i class="md md-chat"></i>
</a>
