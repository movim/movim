<div class="pack">
    <ul class="list thick">
        <li>
            <span class="primary icon yellow">
                <i class="material-symbols">family_star</i>
            </span>
            <div>
                <p>{$c->__('emojisconfig.favorites_title')}</p>
                <p>{$c->__('emojisconfig.favorites_text')}</p>
            </div>
        </li>
    </ul>
    <div class="emojis">
        {loop="$favorites"}
            <img class="emoji large" title="{$value->pivot->alias}" data-id="{$value->id}" src="{$value->url}" onclick="EmojisConfig_ajaxAddEditFavoriteForm({$value->id})">
        {/loop}
    </div>
</div>
{loop="$packs"}
    <div class="pack">
        <ul class="list thick">
            <li>
                <div>
                    <p class="normal">{$value->name}</p>
                    <p>
                        {if="$value->description"}
                            {$value->description}
                        {/if}
                        {if="$value->homepage"}
                            •
                            <a href="{$value->homepage}" target="_blank">{$c->__('general.website')}</a>
                        {/if}
                        {if="$value->license"}
                            •
                            {$value->license}
                        {/if}
                    </p>
                </div>
            </li>
        </ul>
        <div class="emojis">
            {loop="$value->emojis"}
                <img title="{$value->aliasPlaceholder}" class="emoji large {if="$favorites->keys()->contains($value->id)"}favorite{/if}" src="{$value->url}" onclick="EmojisConfig_ajaxAddEditFavoriteForm({$value->id})">
            {/loop}
        </div>
    </div>
{/loop}
