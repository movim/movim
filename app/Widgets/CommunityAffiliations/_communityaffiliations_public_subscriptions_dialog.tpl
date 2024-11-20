<section class="scroll">
    {if="$subscriptions->isNotEmpty()"}
        {autoescape="off"}
            {$c->preparePublicSubscriptionsList($subscriptions)}
        {/autoescape}
    {/if}
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</footer>
