<section>
    <ul class="simple">
        <li class="subheader">{$c->__('roster.search')}</li>
        <li>
            <form name="add">
                <div>
                    <input 
                        name="searchjid" 
                        type="email"
                        title="{$c->__('roster.jid')}"
                        placeholder="user@server.tld"
                        {if="$jid != null"}
                            value="{$jid}"
                        {/if}
                        onkeyup="if(this.validity.valid == true) { {$search} }"
                    />
                    <label for="searchjid">{$c->__('roster.add_contact_info1')}</label>
                </div>
            </form>
        </li>
    </ul>
    <div id="search_results">

    </div>
</section>
<div class="actions">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="{$add} Dialog.clear()" class="button flat">
        {$c->__('button.add')}
    </a>
</div>
