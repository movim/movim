<section>
    <h3>{$c->__('group.sure')}</h3>
    {if="$item"}
        <br />
        <h4 class="gray">
            {$c->__('group.unsubscribe_text')} : {$item->name}
        </h4>
    {/if}
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a
        onclick="Group_ajaxUnsubscribe('{$server}', '{$node}'); Dialog.clear()"
        class="button flat">
        {$c->__('group.unsubscribe')}
    </a>
</div>
