<section>
    <h3>{$c->__('emojisconfig.dialog_title')}</h3>

    <form name="add_edit_favorite" onsubmit="return false;">
        <div>
            <ul class="list fill">
                <li>
                    <span class="primary icon">
                        <img src="{$emoji->url}">
                    </span>
                    <div>
                        <input name="alias"
                            id="alias"
                            type="text"
                            pattern="[a-z0-9\-]+"
                            {if="$favorite"}
                                value="{$favorite->pivot->alias}"
                            {else}
                                value="{$emoji->aliasPlaceholder}"
                            {/if}
                            placeholder="{$emoji->aliasPlaceholder}"/>
                        <label for="alias">{$c->__('message.emoji_help')}</label>
                    </div>
                </li>
            </ul>
        </div>
        <input type="hidden" name="emojiid" value="{$emoji->id}">
    </form>
</section>
<div class="no_bar">
    {if="$favorite"}
        <button onclick="EmojisConfig_ajaxRemoveFavorite({$emoji->id});" class="button flat red">
            {$c->__('button.remove')}
        </button>
    {/if}
    <button onclick="Dialog_ajaxClear();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="EmojisConfig_ajaxAddEditFavorite(MovimUtils.formToJson('add_edit_favorite'))" class="button flat">
        {$c->__('button.save')}
    </button>
</div>
