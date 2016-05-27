<section class="scroll">
    <form name="command" data-sessionid="{$attributes->sessionid}" data-node="{$attributes->node}"  onsubmit="return false;">
        {$form}
    </form>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    {if="$actions != null"}
        {if="isset($actions->next)"}
            <a onclick="AdHoc.submit()" class="button flat">
                {$c->__('button.next')}
            </a>
        {/if}
        {if="isset($actions->previous)"}
            <a onclick="" class="button flat">
                {$c->__('button.previous')}
            </a>
        {/if}
        {if="isset($actions->cancel)"}
            <a onclick="" class="button flat">
                {$c->__('button.cancel')}
            </a>
        {/if}
        {if="isset($actions->complete)"}
            <!--<a onclick="" class="button flat">
                {$c->__('button.submit')}
            </a>-->
        {/if}
    {/if}
</div>
