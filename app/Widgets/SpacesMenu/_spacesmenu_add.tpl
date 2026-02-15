<section>
    <form name="spacesmenu_add">
        <h3>{$c->__('spacesmenu.invite_space_title')}</h3>

        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">communities</i>
                    </span>
                    <div>
                        <input name="uri" placeholder="xmpp:server.com?;node=key" required/>
                        <label for="uri">{$c->__('spacesmenu.invitation_key')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>

    {if="$c->me->hasSpaces()"}
        <br />
        <hr />

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
    {/if}
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
    <button
        class="button flat"
        onclick="SpacesMenu_ajaxJoinFromUri(MovimUtils.formToJson('spacesmenu_add')); Dialog_ajaxClear();"
        >
        {$c->__('button.add')}
    </button>
</footer>
