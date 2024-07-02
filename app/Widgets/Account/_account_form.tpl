<section class="scroll">
    <form name="register" data-sessionid="{$attributes->sessionid}" data-node="{$attributes->node}">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </form>
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="Account_ajaxRegister('{$from}', MovimUtils.formToJson('register')); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.submit')}
    </button>
    {if="$actions != null"}
        {if="isset($actions->next)"}
            <button onclick="AdHoc.submit()" class="button flat">
                {$c->__('button.next')}
            </button>
        {/if}
        {if="isset($actions->previous)"}
            <button class="button flat">
                {$c->__('button.previous')}
            </button>
        {/if}
        {if="isset($actions->cancel)"}
            <button class="button flat">
                {$c->__('button.cancel')}
            </button>
        {/if}
        {if="isset($actions->complete)"}
            <!--<a onclick="" class="button flat">
                {$c->__('button.submit')}
            </a>-->
        {/if}
    {/if}
</div>
