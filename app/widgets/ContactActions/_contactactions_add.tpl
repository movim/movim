<section>
    <h3>{$c->__('roster.add_title')}</h3>
    <ul class="list thick">
        {$url = $contact->getPhoto('s')}
        <li>
            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$contact->jid|stringToColor}">
                    {$contact->getTrueName()|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line">
                {$contact->getTrueName()}
            </p>
            <p>{$contact->jid}</p>
        </li>
        <li>
            <form name="add" onsubmit="return false;">
                <input
                    name="searchjid"
                    id="searchjid"
                    type="hidden"
                    value="{$contact->jid}"
                />
                <div>
                    <datalist id="group_list" style="display: none;">
                        {if="is_array($groups)"}
                            {loop="$groups"}
                                <option value="{$value}"/>
                            {/loop}
                        {/if}
                    </datalist>
                    <input
                        name="group"
                        list="group_list"
                        id="group"
                        class="tiny"
                        placeholder="{$c->__('edit.group')}"
                        value="{$contact->groupname}"/>
                    <label for="group">{$c->__('edit.group')}</label>
                </div>
            </form>
        </li>
    </ul>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="ContactActions_ajaxAdd(MovimUtils.formToJson('add'))" class="button flat">
        {$c->__('button.add')}
    </button>
</div>
