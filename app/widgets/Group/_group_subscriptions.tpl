<section class="scroll">
    <ul class="thin divided simple">
        <li class="subheader">
            {$c->__('group.subscriptions')}
            <span class="info">{$subscriptions|count}</span>
        </li>
        {loop="$subscriptions"}
            <li>
                <a href="{$c->route('contact', $value.jid)}">
                    {$value.jid}
                </a>
            </li>
        {/loop}
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
