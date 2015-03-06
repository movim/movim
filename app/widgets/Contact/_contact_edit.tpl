<section>
    <h3>{$c->__('edit.title')}</h3>
    <form name="manage">
        <div>
            <input 
                name="alias" 
                id="alias" 
                class="tiny" 
                placeholder="{$c->__('edit.alias')}" 
                {if="$contact->rostername"}
                    value="{$contact->rostername}"
                {else}
                    value="{$contact->jid}"
                {/if}"/>
            <label for="alias">{$c->__('edit.alias')}</label>
        </div>
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
        <input type="hidden" name="jid" value="{$contact->jid}"/>
    </form>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="{$submit} Dialog.clear()">
        {$c->__('button.edit')}
    </a>
</div>
