<div class="placeholder">
    <i class="material-symbols">communities</i>

    {if="$subscription && $subscription->info"}
        <h1>
            {autoescape="off"}{$subscription->info->name|addEmojis}{/autoescape}
        </h1>
    {/if}
</div>