<section class="scroll">
    <form name="command" data-sessionid="{$attributes->sessionid}" data-node="{$attributes->node}"  onsubmit="return false;">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>

    {if="$status != 'completed'"}
        {if="$actions != null"}
            {loop="$actions"}{loop="$value"}
                <button id="adhoc_action" data-jid="{$jid}" data-action="{$value->getName()}"
                    onclick="AdHoc.submit(this.dataset.jid, this.dataset.action)" class="button flat">
                    {if="$value->getName() == 'next'"}
                        {$c->__('button.next')}
                    {elseif="$value->getName() == 'prev'"}
                        {$c->__('button.previous')}
                    {elseif="$value->getName() == 'complete'"}
                        {$c->__('button.submit')}
                    {/if}
                </button>
            {/loop}{/loop}
        {else}
            <button id="adhoc_action" data-jid="{$jid}" onclick="AdHoc.submit(this.dataset.jid, 'complete')" class="button flat">
                {$c->__('button.submit')}
            </button>
        {/if}
    {/if}
</footer>
