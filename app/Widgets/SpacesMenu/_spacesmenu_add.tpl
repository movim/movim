<section>
    <h3>{$c->__('spacesmenu.add_space_title')}</h3>

    {if="$c->me->hasSpaces()"}
        <ul class="list active">
            <li class onclick="SpacesMenu_ajaxCreate()">
                <span class="primary icon gray">
                    <i class="material-symbols">add</i>
                </span>
                <span class="control icon">
                    <i class="material-symbols">chevron_forward</i>
                </span>
                <div>
                    <p class="line">{$c->__('spacesmenu.create_space_title')}</p>
                </div>
            </li>
        </ul>

        <hr />
        <br />
    {/if}

    <h4>{if="isset($uri)"}{$c->__('spacesmenu.join_space_title')}{else}{$c->__('spacesmenu.invite_space_title')}{/if}</h4>

    {if="isset($info)"}
        <ul class="list middle">
            <li>
                <span class="primary icon bubble space">
                    <img src="{$info->getPicture(placeholder: $info->name)}">
                </span>
                <div>
                    <p class="line">
                        {autoescape="off"}{$info->name|addEmojis}{/autoescape}
                    </p>
                    <p class="line two">
                        {if="!empty($info->description)"}
                            {autoescape="off"}{$info->description|addEmojis}{/autoescape}
                        {else}
                            {$info->server}
                        {/if}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    <form name="spacesmenu_add">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">join</i>
                    </span>
                    <span class="control icon divided gray active">
                        <i class="material-symbols">search</i>
                    </span>
                    <div>
                        <input name="uri" placeholder="https://… or xmpp:server.com?;node=key" {if="isset($uri)"}value="{$uri}"{/if} oninput="MovimUtils.addClass('#spacesmenu_discover_add', 'disabled')" onblur="SpacesMenu_ajaxDiscoSpace(this.value)" required/>
                        <label for="uri">{$c->__('spacesmenu.invitation_key')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>

    <div id="spacesmenu_discover_result"></div>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
    <button
        class="button color green disabled"
        id="spacesmenu_discover_add"
        >
        {$c->__('button.join')}
    </button>
</footer>
