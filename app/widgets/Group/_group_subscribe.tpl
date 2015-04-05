<section>
    <h3>{$c->__('group.subscribe')}</h3>
    {if="$item"}
        <br />
        <h4 class="gray">
            {$item->name}
        </h4>
    {/if}
    <ul class="simple">
        <li>
            <form name="subscribe">
                <div>
                    <input 
                        name="label" 
                        type="text"
                        title="{$c->__('group.label_label')}"
                        placeholder="My Group Name"
                        {if="$item"}
                            value="{$item->name}"
                        {/if}
                    />
                    <label for="label">{$c->__('group.label_label')}</label>
                </div>
            </form>
        </li>
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a
        onclick="Group_ajaxSubscribe(movim_form_to_json('subscribe'), '{$server}', '{$node}'); Dialog.clear()"
        class="button flat">
        {$c->__('group.subscribe')}
    </a>
</div>
