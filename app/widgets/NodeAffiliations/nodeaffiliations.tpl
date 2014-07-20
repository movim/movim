{if="$pepfilter"}
<div class="tabelem" title="{$c->__('affiliations.title')}" id="groupmemberlist">
    <h1 class="paddedtopbottom"><i class="fa fa-key"></i> {$c->__('affiliations.title')}</h1>
    <div class="posthead paddedtopbottom">
        <a 
            class="button color green" 
            onclick="{$getaffiliations} this.parentNode.style.display = 'none'">
            <i class="fa fa-key"></i> {$c->__('affiliations.get')}
        </a>
    </div>
    
    <div id="memberlist" class="paddedtop"></div>
</div>
{/if}
