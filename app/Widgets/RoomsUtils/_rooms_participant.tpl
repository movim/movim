<section>
    <header class="big color {$contact->color}"
        style="background-image: linear-gradient(to bottom, rgba(23,23,23,0.8) 0%, rgba(23,23,23,0.5) 100%), url('{$conference->getPicture(\Movim\ImageSize::XXL)}');"
        >
        <ul class="list thick">
            <li>
                <span class="primary icon bubble status active {$presence->presencekey}">
                    <img loading="lazy" src="{$presence->conferencePicture}">
                </span>
                <div>
                    <p class="line">
                        {$presence->resource}
                    </p>
                    <p class="line">
                        {$contact->id}
                    </p>
                </div>
            </li>
        </ul>
    </header>

    <div id="{$contact->id|cleanupId}-vcard">
        {autoescape="off"}
            {$c->prepareVcard($contact)}
        {/autoescape}
    </div>

    <ul class="list">
        {if="$presence->capability"}
            <li class="block">
                <span class="control icon gray">
                    <i class="material-symbols">
                        {$presence->capability->getDeviceIcon()}
                    </i>
                </span>
                <div>
                    <p class="line">
                        {$presence->capability->name}
                    </p>
                    {if="$presence->capability->identities()->first() && isset($clienttype[$presence->capability->identities()->first()->type])"}
                        <p class="line">
                            {$clienttype[$presence->capability->identities()->first()->type]}
                        </p>
                    {/if}
                </div>
            </li>
        {/if}

        {if="$presence->hats->isNotEmpty() || $presence->mucaffiliation == 'owner' || $presence->mucaffiliation == 'admin'"}
            <li class="subheader">
                <div>
                    <p>{$c->__('communityaffiliation.roles')}</p>
                </div>
            </li>
            <li>
                <div>
                    <p></p>
                    <p class="all">
                        {if="$presence->mucaffiliation == 'owner'"}
                            <span class="chip thin" title="{$c->__('room.affiliation_owner')}">
                                <i class="material-symbols icon fill yellow">star</i>
                                    {$presence->affiliationTxt}
                            </span>
                        {elseif="$presence->mucaffiliation == 'admin'"}
                            <span class="chip thin" title="{$c->__('room.affiliation_owner')}">
                                <i class="material-symbols icon fill gray">star</i>
                                {$presence->affiliationTxt}
                            </span>
                        {/if}
                        {loop="$presence->hats"}
                            <span class="chip thin" title="{$value->title}">
                                <i class="material-symbols fill icon {$value->color}">circle</i>
                                {$value->title}
                            </span>
                        {/loop}
                    </p>
                </div>
            </li>
        {/if}
    </ul>

    <br />
    <hr />

    <ul class="list active">
        <li class="subheader">
            <div>
                <p>{$c->__('adhoc.title')}</p>
            </div>
        </li>
        <li onclick="Chat.quoteMUC('{$presence->resource}', true); Dialog_ajaxClear();">
            <span class="primary icon gray">
                <i class="material-symbols">format_quote</i>
            </span>
            <div>
                <p class="line">
                    {$c->__('button.quote')}
                </p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</footer>
