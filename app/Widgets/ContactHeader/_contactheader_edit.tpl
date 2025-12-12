<section>
    <ul class="list thick">
        <li>
            <span class="control icon active white" onclick="Notifications_ajaxDeleteContact('{$jid|echapJS}')"
                title="{$c->__('button.delete')}">
                <i class="material-symbols">delete</i>
            </span>
            <div>
                <p>{$c->__('edit.title')}</p>
                <p>{$jid}</p>
            </div>
        </li>
    </ul>
    <form name="manage">
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
                {if="is_array($groups)"}
                    {loop="$groups"}
                        <option value="{$value ?? ''}"/>
                    {/loop}
                {/if}
            </datalist>
            <input
                name="group"
                list="group_list"
                id="group"
                placeholder="{$c->__('edit.group')}"
                value="{$contact->group ?? ''}"/>
            <label for="group">{$c->__('edit.group')}</label>
        </div>
        <input type="hidden" name="jid" value="{$contact->id}"/>
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="ContactHeader_ajaxEditSubmit(MovimUtils.formToJson('manage')); Dialog_ajaxClear()">
        {$c->__('button.save')}
    </button>
</footer>
