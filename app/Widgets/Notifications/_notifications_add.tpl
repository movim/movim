<section>
    <h3>{$c->__('roster.add_title')}</h3>
    <ul class="list thick">
        <li>
            <span class="primary icon bubble">
                <img src="{$contact->getPicture()}">
            </span>
            <div>
                <p class="line">
                    {$contact->truename}
                </p>
                <p>{$contact->id}</p>
            </div>
        </li>
    </ul>
    <form name="add" onsubmit="return false;">
        <input
            name="searchjid"
            id="searchjid"
            type="hidden"
            value="{$contact->id}"
        />
        <div>
            <input
                name="alias"
                id="alias"
                placeholder="{$c->__('edit.alias')}"
                {if="$contact->name"}
                    value="{$contact->name}"
                {else}
                    value="{$contact->truename}"
                {/if}"/>
            <label for="alias">{$c->__('edit.alias')}</label>
        </div>
        <div>
            <datalist id="group_list" style="display: none;">
                {loop="$groups"}
                    <option value="{$value}"/>
                {/loop}
            </datalist>
            <input
                name="group"
                list="group_list"
                id="group"
                placeholder="{$c->__('edit.group')}"
                value="{$contact->groupname ?? ''}"/>
            <label for="group">{$c->__('edit.group')}</label>
        </div>
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="Notifications_ajaxAdd(MovimUtils.formToJson('add'))" class="button flat">
        {$c->__('button.add')}
    </button>
</footer>
