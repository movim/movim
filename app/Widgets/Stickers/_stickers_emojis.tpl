<section>
    {if="$reactionsrestrictions"}
        <div class="emojis_grid">
            {loop="$reactionsrestrictions"}
                {$codepoint = emojiToCodePoint($value)}

                {if="strlen($codepoint) < 6"}
                    <img data-emoji="{$value}" class="emoji large" src="{$c->emoji($codepoint)}">
                {/if}
            {/loop}
        </div>
    {else}
        <div class="emojis_grid results"></div>

        {if="$gotemojis"}
            {if="$favorites->isEmpty()"}
                <ul class="list thick active">
                    <li onclick="MovimUtils.reload('{$c->route('configuration')}')">
                        <span class="primary icon yellow">
                            <i class="material-symbols">family_star</i>
                        </span>
                        <span class="control icon gray">
                            <i class="material-symbols">chevron_right</i>
                        </span>
                        <div>
                            <p>{$c->__('emojisconfig.favorites_title')}</p>
                            <p>{$c->__('emojisconfig.no_emojis_yet')}</p>
                        </div>
                    </li>
                </ul>
            {else}
                <div class="emojis_grid main">
                    {loop="$favorites"}
                        <img title="{$value->pivot->alias}" data-emoji=":{$value->pivot->alias}:" alt=":{$value->pivot->alias}:" class="emoji large" src="{$value->url}">
                    {/loop}
                </div>
            {/if}
        {/if}

        {if="!empty($suggested)"}
            <div class="emojis_grid main">
                {loop="$suggested"}
                    <img title="{$value}" data-emoji="{$value}" class="emoji large" src="{$c->emoji(emojiToCodePoint($value))}">
                {/loop}
            </div>
        {/if}

        <div class="emojis_grid">
            <img data-emoji="♥" alt=":hearts:" class="emoji large" src="{$c->emoji('2665')}">
            <img data-emoji="👍" class="emoji large" src="{$c->emoji('1f44d')}">
            <img data-emoji="👎" class="emoji large" src="{$c->emoji('1f44e')}">
            <img data-emoji="😂" class="emoji large" src="{$c->emoji('1f602')}">
            <img data-emoji="😊" class="emoji large" src="{$c->emoji('1f60a')}">
            <img data-emoji="😉" class="emoji large" src="{$c->emoji('1f609')}">

            <img data-emoji="😀" class="emoji large" src="{$c->emoji('1f600')}">
            <img data-emoji="😁" class="emoji large" src="{$c->emoji('1f601')}">
            <img data-emoji="😃" class="emoji large" src="{$c->emoji('1f603')}">
            <img data-emoji="😄" class="emoji large" src="{$c->emoji('1f604')}">
            <img data-emoji="😅" class="emoji large" src="{$c->emoji('1f605')}">
            <img data-emoji="🤭" class="emoji large" src="{$c->emoji('1f92d')}">

            <img data-emoji="🧐" class="emoji large" src="{$c->emoji('1f9d0')}">
            <img data-emoji="🤯" class="emoji large" src="{$c->emoji('1f92f')}">
            <img data-emoji="🤑" class="emoji large" src="{$c->emoji('1f911')}">
            <img data-emoji="🤤" class="emoji large" src="{$c->emoji('1f924')}">
            <img data-emoji="🙄" class="emoji large" src="{$c->emoji('1f644')}">
            <img data-emoji="🤔" class="emoji large" src="{$c->emoji('1f914')}">

            <img data-emoji="🤗" class="emoji large" src="{$c->emoji('1f917')}">
            <img data-emoji="🤩" class="emoji large" src="{$c->emoji('1f929')}">
            <img data-emoji="🤨" class="emoji large" src="{$c->emoji('1f928')}">
            <img data-emoji="🤪" class="emoji large" src="{$c->emoji('1f92a')}">
            <img data-emoji="🤢" class="emoji large" src="{$c->emoji('1f922')}">
            <img data-emoji="😆" class="emoji large" src="{$c->emoji('1f606')}">

            <img data-emoji="😇" class="emoji large" src="{$c->emoji('1f607')}">
            <img data-emoji="😈" class="emoji large" src="{$c->emoji('1f608')}">
            <img data-emoji="😋" class="emoji large" src="{$c->emoji('1f60b')}">
            <img data-emoji="😌" class="emoji large" src="{$c->emoji('1f60c')}">
            <img data-emoji="😍" class="emoji large" src="{$c->emoji('1f60d')}">
            <img data-emoji="😎" class="emoji large" src="{$c->emoji('1f60e')}">

            <img data-emoji="😏" class="emoji large" src="{$c->emoji('1f60f')}">
            <img data-emoji="😐" class="emoji large" src="{$c->emoji('1f610')}">
            <img data-emoji="😑" class="emoji large" src="{$c->emoji('1f611')}">
            <img data-emoji="😒" class="emoji large" src="{$c->emoji('1f612')}">
            <img data-emoji="😓" class="emoji large" src="{$c->emoji('1f613')}">
            <img data-emoji="😔" class="emoji large" src="{$c->emoji('1f614')}">

            <img data-emoji="😕" class="emoji large" src="{$c->emoji('1f615')}">
            <img data-emoji="😖" class="emoji large" src="{$c->emoji('1f616')}">
            <img data-emoji="😗" class="emoji large" src="{$c->emoji('1f617')}">
            <img data-emoji="😘" class="emoji large" src="{$c->emoji('1f618')}">
            <img data-emoji="😙" class="emoji large" src="{$c->emoji('1f619')}">
            <img data-emoji="😚" class="emoji large" src="{$c->emoji('1f61a')}">

            <img data-emoji="😛" class="emoji large" src="{$c->emoji('1f61b')}">
            <img data-emoji="😜" class="emoji large" src="{$c->emoji('1f61c')}">
            <img data-emoji="😝" class="emoji large" src="{$c->emoji('1f61d')}">
            <img data-emoji="😞" class="emoji large" src="{$c->emoji('1f61e')}">
            <img data-emoji="😟" class="emoji large" src="{$c->emoji('1f61f')}">
            <img data-emoji="😠" class="emoji large" src="{$c->emoji('1f620')}">

            <img data-emoji="😡" class="emoji large" src="{$c->emoji('1f621')}">
            <img data-emoji="😢" class="emoji large" src="{$c->emoji('1f622')}">
            <img data-emoji="😣" class="emoji large" src="{$c->emoji('1f623')}">
            <img data-emoji="😤" class="emoji large" src="{$c->emoji('1f624')}">
            <img data-emoji="😥" class="emoji large" src="{$c->emoji('1f625')}">
            <img data-emoji="😦" class="emoji large" src="{$c->emoji('1f626')}">

            <img data-emoji="😧" class="emoji large" src="{$c->emoji('1f627')}">
            <img data-emoji="😨" class="emoji large" src="{$c->emoji('1f628')}">
            <img data-emoji="😩" class="emoji large" src="{$c->emoji('1f629')}">
            <img data-emoji="😪" class="emoji large" src="{$c->emoji('1f62a')}">
            <img data-emoji="😫" class="emoji large" src="{$c->emoji('1f62b')}">
            <img data-emoji="😬" class="emoji large" src="{$c->emoji('1f62c')}">

            <img data-emoji="😭" class="emoji large" src="{$c->emoji('1f62d')}">
            <img data-emoji="😮" class="emoji large" src="{$c->emoji('1f62e')}">
            <img data-emoji="😯" class="emoji large" src="{$c->emoji('1f62f')}">
            <img data-emoji="😰" class="emoji large" src="{$c->emoji('1f630')}">
            <img data-emoji="😱" class="emoji large" src="{$c->emoji('1f631')}">
            <img data-emoji="😲" class="emoji large" src="{$c->emoji('1f632')}">

            <img data-emoji="😳" class="emoji large" src="{$c->emoji('1f633')}">
            <img data-emoji="😴" class="emoji large" src="{$c->emoji('1f634')}">
            <img data-emoji="👊" class="emoji large" src="{$c->emoji('1f44a')}">
            <img data-emoji="👋" class="emoji large" src="{$c->emoji('1f44b')}">
            <img data-emoji="👌" class="emoji large" src="{$c->emoji('1f44c')}">
            <img data-emoji="👏" class="emoji large" src="{$c->emoji('1f44f')}">
        </div>
    {/if}
</section>

<ul class="list">
    <li class="search">
        <form name="search" onsubmit="return false;">
            <div>
                <input name="keyword" autocomplete="off"
                    title="{$c->__('message.emoji_help')}"
                    placeholder="{$c->__('message.emoji_help')}"
                    oninput="Chat.checkEmojis(this.value, true, true);
                        Stickers.setEmojisEvent({if=" $mid"}{$mid}{/if});"
                    type=" text">
            </div>
        </form>
    </li>
</ul>
