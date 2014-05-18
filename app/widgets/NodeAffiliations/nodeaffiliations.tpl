{if="$pepfilter"}
<div class="tabelem" title="{$c->__('affiliations.title')}" id="groupmemberlist">
    <h1>{$c->__('affiliations.title')}</h1>
    <div class="posthead">
        <a 
            class="button icon users color green" 
            onclick="{$getaffiliations} this.parentNode.style.display = 'none'">
                {$c->__('affiliations.get')}
        </a>
    </div>
    
    <div id="memberlist" class="paddedtop"></div>
</div>
{/if}
