{if="$pepfilter"}
<div id="subscriptions" class="tabelem" title="{$c->__('subscriptions.title')}">
    <h1 class="paddedtopbottom"><i class="fa fa-users"></i> {$c->__('subscriptions.info')}</h1>
    <div class="posthead paddedtopbottom">
        <a 
            class="button color green" 
            onclick="{$getsubscriptions} this.parentNode.style.display = 'none'">
            <i class="fa fa-users"></i> {$c->__('subscriptions.get')}
        </a>
    </div>
    
    <div id="subscriptionslist" class="padded"></div>
</div>
{/if}
