<section>
    <h3>{$c->__('group.delete_title')}</h3>
    <br />
    {if="$clean"}
        <h4 class="gray">{$c->__('group.delete_clean_text')}</h4>
    {else}
        <h4 class="gray">{$c->__('group.delete_text')}</h4>
    {/if}
    <br />
    <h4 class="gray">{$server}/{$node}</h4>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="Group_ajaxDeleteConfirm('{$server}', '{$node}'); Dialog.clear()">
        {$c->__('button.remove')}
    </a>
</div>
