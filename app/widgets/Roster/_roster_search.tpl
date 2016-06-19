<section>
    <h3>{$c->__('roster.search_pod')}</h3>
    <ul class="list">
        <li>
            <form name="add" onsubmit="return false;">
                <div>
                    <input
                        name="searchjid"
                        type="email"
                        title="{$c->__('roster.jid')}"
                        placeholder="user@server.tld"
                        {if="$jid != null"}
                            value="{$jid}"
                        {/if}
                        onkeyup="{$search}"
                    />
                    <label for="searchjid">{$c->__('roster.add_contact_info1')}</label>
                </div>
            </form>
        </li>
    </ul>
    <div id="search_results">

    </div>
</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="{$add} Dialog_ajaxClear()" class="button flat">
        {$c->__('button.add')}
    </a>
</div>
