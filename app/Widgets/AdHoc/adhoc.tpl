{if="$to != null"}
    <div id="adhoc_widget_{$to|cleanupId}"
        class="adhoc_widget tabelem"
        title="{$c->__('adhoc.title')}"
        data-mobileicon="terminal">
        <div class="placeholder">
            <i class="material-symbols">terminal</i>
            <h1>{$c->__('adhoc.title')}</h1>
        </div>
    </div>
{/if}