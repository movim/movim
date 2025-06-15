<div class="radio">
    <input name="accentcolor" value="{$color}" id="accentcolor_{$color}" type="radio"
        {if="$configuration->accentcolor == $color"}checked{/if}>
    <label for="accentcolor_{$color}" style="border-color: var(--p-{$color}); background-color: var(--p-{$color})"
        onclick="Config.setAccentColor('{$color}')"></label>
</div>