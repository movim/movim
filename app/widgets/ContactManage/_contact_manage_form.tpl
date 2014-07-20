<h2>{$c->__('title')}</h2>
<form name="manage">
    <input type="hidden" name="jid" value="{$rl->jid}"/>
    <div class="element large mini">
        <input 
            name="alias" 
            id="alias" 
            class="tiny" 
            placeholder="{$c->__('alias')}" 
            value="{$rl->rostername}"/>
    </div>
    <div class="element large mini">
        <datalist id="group" style="display: none;">
            {if="is_array($groups)"}
                {loop="$groups"}
                    <option value="{$value}"/>
                {/loop}
            {/if}
        </datalist>
        <input 
            name="group" 
            list="group" 
            id="alias" 
            class="tiny" 
            placeholder="{$c->__('group')}" 
            value="{$rl->groupname}"/>
    </div>
    
    <a 
        name="submit" 
        class="button black" 
        onclick="{$submit} this.style.display = 'none';">
        <i class="fa fa-check"></i> {$c->__('button.save')}
    </a>
</form>
