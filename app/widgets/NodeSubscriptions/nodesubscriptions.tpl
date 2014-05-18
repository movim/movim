{if="$pepfilter"}
<div id="subscriptions" class="tabelem" title="{$c->__('subscriptions.title')}">
    <h1>{$c->__('subscriptions.info')}</h1>
    <div class="posthead">
        <a 
            class="button icon users color green" 
            onclick="{$getsubscriptions} this.parentNode.style.display = 'none'">
            {$c->__('subscriptions.get')}
        </a>
    </div>
    
    <div id="subscriptionslist" class="padded"></div>
</div>
{/if}
