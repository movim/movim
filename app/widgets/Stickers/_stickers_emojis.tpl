<ul id="emojisearchbar" class="list fill thin">
    <li>
        <span class="primary icon gray">
            <i class="material-icons">search</i>
        </span>
        <form name="search" onsubmit="return false;">
            <div>
                <input name="keyword" autocomplete="off"
                    title="{$c->__('message.emoji_help')}"
                    placeholder="{$c->__('message.emoji_help')}"
                    oninput="
                        Chat.checkEmojis(this.value, true, true);
                        Stickers.setEmojisEvent({$mid});
                    "
                    type="text">
            </div>
        </form>
    </li>
</ul>
<div class="emojis results"></div>

<div class="emojis main">
    <img data-emoji="♥" alt=":hearts:" class="emoji large" src="{$c->getSmileyPath('2665')}">
    <img data-emoji="👍" class="emoji large" src="{$c->getSmileyPath('1f44d')}">
    <img data-emoji="👎" class="emoji large" src="{$c->getSmileyPath('1f44e')}">
    <img data-emoji="😂" class="emoji large" src="{$c->getSmileyPath('1f602')}">
    <img data-emoji="😊" class="emoji large" src="{$c->getSmileyPath('1f60a')}">
    <img data-emoji="😉" class="emoji large" src="{$c->getSmileyPath('1f609')}">
</div>

<div class="emojis">
    <img data-emoji="😀" class="emoji large" src="{$c->getSmileyPath('1f600')}">
    <img data-emoji="😁" class="emoji large" src="{$c->getSmileyPath('1f601')}">
    <img data-emoji="😃" class="emoji large" src="{$c->getSmileyPath('1f603')}">
    <img data-emoji="😄" class="emoji large" src="{$c->getSmileyPath('1f604')}">
    <img data-emoji="😅" class="emoji large" src="{$c->getSmileyPath('1f605')}">
    <img data-emoji="🤭" class="emoji large" src="{$c->getSmileyPath('1f92d')}">

    <img data-emoji="🧐" class="emoji large" src="{$c->getSmileyPath('1f9d0')}">
    <img data-emoji="🤯" class="emoji large" src="{$c->getSmileyPath('1f92f')}">
    <img data-emoji="🤑" class="emoji large" src="{$c->getSmileyPath('1f911')}">
    <img data-emoji="🤤" class="emoji large" src="{$c->getSmileyPath('1f924')}">
    <img data-emoji="🙄" class="emoji large" src="{$c->getSmileyPath('1f644')}">
    <img data-emoji="🤔" class="emoji large" src="{$c->getSmileyPath('1f914')}">

    <img data-emoji="🤗" class="emoji large" src="{$c->getSmileyPath('1f917')}">
    <img data-emoji="🤩" class="emoji large" src="{$c->getSmileyPath('1f929')}">
    <img data-emoji="🤨" class="emoji large" src="{$c->getSmileyPath('1f928')}">
    <img data-emoji="🤪" class="emoji large" src="{$c->getSmileyPath('1f92a')}">
    <img data-emoji="🤢" class="emoji large" src="{$c->getSmileyPath('1f922')}">
    <img data-emoji="😆" class="emoji large" src="{$c->getSmileyPath('1f606')}">

    <img data-emoji="😇" class="emoji large" src="{$c->getSmileyPath('1f607')}">
    <img data-emoji="😈" class="emoji large" src="{$c->getSmileyPath('1f608')}">
    <img data-emoji="😋" class="emoji large" src="{$c->getSmileyPath('1f60b')}">
    <img data-emoji="😌" class="emoji large" src="{$c->getSmileyPath('1f60c')}">
    <img data-emoji="😍" class="emoji large" src="{$c->getSmileyPath('1f60d')}">
    <img data-emoji="😎" class="emoji large" src="{$c->getSmileyPath('1f60e')}">

    <img data-emoji="😏" class="emoji large" src="{$c->getSmileyPath('1f60f')}">
    <img data-emoji="😐" class="emoji large" src="{$c->getSmileyPath('1f610')}">
    <img data-emoji="😑" class="emoji large" src="{$c->getSmileyPath('1f611')}">
    <img data-emoji="😒" class="emoji large" src="{$c->getSmileyPath('1f612')}">
    <img data-emoji="😓" class="emoji large" src="{$c->getSmileyPath('1f613')}">
    <img data-emoji="😔" class="emoji large" src="{$c->getSmileyPath('1f614')}">

    <img data-emoji="😕" class="emoji large" src="{$c->getSmileyPath('1f615')}">
    <img data-emoji="😖" class="emoji large" src="{$c->getSmileyPath('1f616')}">
    <img data-emoji="😗" class="emoji large" src="{$c->getSmileyPath('1f617')}">
    <img data-emoji="😘" class="emoji large" src="{$c->getSmileyPath('1f618')}">
    <img data-emoji="😙" class="emoji large" src="{$c->getSmileyPath('1f619')}">
    <img data-emoji="😚" class="emoji large" src="{$c->getSmileyPath('1f61a')}">

    <img data-emoji="😛" class="emoji large" src="{$c->getSmileyPath('1f61b')}">
    <img data-emoji="😜" class="emoji large" src="{$c->getSmileyPath('1f61c')}">
    <img data-emoji="😝" class="emoji large" src="{$c->getSmileyPath('1f61d')}">
    <img data-emoji="😞" class="emoji large" src="{$c->getSmileyPath('1f61e')}">
    <img data-emoji="😟" class="emoji large" src="{$c->getSmileyPath('1f61f')}">
    <img data-emoji="😠" class="emoji large" src="{$c->getSmileyPath('1f620')}">

    <img data-emoji="😡" class="emoji large" src="{$c->getSmileyPath('1f621')}">
    <img data-emoji="😢" class="emoji large" src="{$c->getSmileyPath('1f622')}">
    <img data-emoji="😣" class="emoji large" src="{$c->getSmileyPath('1f623')}">
    <img data-emoji="😤" class="emoji large" src="{$c->getSmileyPath('1f624')}">
    <img data-emoji="😥" class="emoji large" src="{$c->getSmileyPath('1f625')}">
    <img data-emoji="😦" class="emoji large" src="{$c->getSmileyPath('1f626')}">

    <img data-emoji="😧" class="emoji large" src="{$c->getSmileyPath('1f627')}">
    <img data-emoji="😨" class="emoji large" src="{$c->getSmileyPath('1f628')}">
    <img data-emoji="😩" class="emoji large" src="{$c->getSmileyPath('1f629')}">
    <img data-emoji="😪" class="emoji large" src="{$c->getSmileyPath('1f62a')}">
    <img data-emoji="😫" class="emoji large" src="{$c->getSmileyPath('1f62b')}">
    <img data-emoji="😬" class="emoji large" src="{$c->getSmileyPath('1f62c')}">

    <img data-emoji="😭" class="emoji large" src="{$c->getSmileyPath('1f62d')}">
    <img data-emoji="😮" class="emoji large" src="{$c->getSmileyPath('1f62e')}">
    <img data-emoji="😯" class="emoji large" src="{$c->getSmileyPath('1f62f')}">
    <img data-emoji="😰" class="emoji large" src="{$c->getSmileyPath('1f630')}">
    <img data-emoji="😱" class="emoji large" src="{$c->getSmileyPath('1f631')}">
    <img data-emoji="😲" class="emoji large" src="{$c->getSmileyPath('1f632')}">

    <img data-emoji="😳" class="emoji large" src="{$c->getSmileyPath('1f633')}">
    <img data-emoji="😴" class="emoji large" src="{$c->getSmileyPath('1f634')}">
    <img data-emoji="👊" class="emoji large" src="{$c->getSmileyPath('1f44a')}">
    <img data-emoji="👋" class="emoji large" src="{$c->getSmileyPath('1f44b')}">
    <img data-emoji="👌" class="emoji large" src="{$c->getSmileyPath('1f44c')}">
    <img data-emoji="👏" class="emoji large" src="{$c->getSmileyPath('1f44f')}">
</div>
