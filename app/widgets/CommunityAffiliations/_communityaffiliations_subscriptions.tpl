<section class="scroll">
    <ul class="list">
        <li class="subheader">
            <div>
                <p>
                    <span class="info">{$subscriptions|count}</span>
                    {$c->__('communityaffiliation.subscriptions')}
                </p>
            </div>
        </li>
        {loop="$subscriptions"}
            {$contact = $c->getContact($value->jid)}
            <li>
                <span class="primary icon bubble">
                    <img src="{$contact->getPhoto('m')}">
                </span>
                <div>
                    <p class="normal line">
                        <a href="{$c->route('contact', $value->jid)}">
                            {$contact->truename}
                        </a>
                    </p>
                    <p>
                        {$contact->jid}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
