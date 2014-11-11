<div class="tabelem paddedtop" title="{$c->__('page.profile')}" id="contactcard">
    <div class="protect red" title="{function="getFlagTitle("red")"}"></div>
        {if="isset($contact)"}
            {$c->prepareContactCard($contact)}
        {/if}
</div>
