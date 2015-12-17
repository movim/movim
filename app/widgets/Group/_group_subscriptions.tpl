<section class="scroll">
    <ul class="list thin divided simple">
        <li class="subheader">
            <p><span class="info">{$subscriptions|count}</span>{$c->__('group.subscriptions')}</p>
        </li>
        {loop="$subscriptions"}
            <li>
                <p class="normal">
                    <a href="{$c->route('contact', $value.jid)}">
                        {$value.jid}
                    </a>
                </p>
            </li>
        {/loop}
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
