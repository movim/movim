<div id="emojisearchbar">
    <ul class="list fill thin">
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
</div>
<div class="emojis">
    <div class="results"></div>
    <img data-emoji="😀" class="emoji large" src="{$c->getSmileyPath('1f600')}">
    <img data-emoji="😁" class="emoji large" src="{$c->getSmileyPath('1f601')}">
    <img data-emoji="😂" class="emoji large" src="{$c->getSmileyPath('1f602')}">
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
    <img data-emoji="😉" class="emoji large" src="{$c->getSmileyPath('1f609')}">
    <img data-emoji="😊" class="emoji large" src="{$c->getSmileyPath('1f60a')}">
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
    <img data-emoji="😵" class="emoji large" src="{$c->getSmileyPath('1f635')}">

    <img data-emoji="👊" class="emoji large" src="{$c->getSmileyPath('1f44a')}">
    <img data-emoji="👋" class="emoji large" src="{$c->getSmileyPath('1f44b')}">
    <img data-emoji="👌" class="emoji large" src="{$c->getSmileyPath('1f44c')}">
    <img data-emoji="👍" class="emoji large" src="{$c->getSmileyPath('1f44d')}">
    <img data-emoji="👎" class="emoji large" src="{$c->getSmileyPath('1f44e')}">
    <img data-emoji="👏" class="emoji large" src="{$c->getSmileyPath('1f44f')}">

    <img data-emoji="🎉" class="emoji large" src="{$c->getSmileyPath('1f389')}">
    <img data-emoji="🎁" class="emoji large" src="{$c->getSmileyPath('1f381')}">
    <img data-emoji="📦" class="emoji large" src="{$c->getSmileyPath('1f4e6')}">
    <img data-emoji="📅" class="emoji large" src="{$c->getSmileyPath('1f4c5')}">
    <img data-emoji="🔒" class="emoji large" src="{$c->getSmileyPath('1f512')}">
    <img data-emoji="🏠" class="emoji large" src="{$c->getSmileyPath('1f3e0')}">

    <img data-emoji="🍌" alt=":banana:" class="emoji large" src="{$c->getSmileyPath('1f34c')}">
    <img data-emoji="🍎" alt=":apple:" class="emoji large" src="{$c->getSmileyPath('1f34e')}">
    <img data-emoji="🌼" alt=":blossom:" class="emoji large" src="{$c->getSmileyPath('1f33c')}">
    <img data-emoji="🌵" alt=":cactus:" class="emoji large" src="{$c->getSmileyPath('1f335')}">
    <img data-emoji="🌹" alt=":rose:" class="emoji large" src="{$c->getSmileyPath('1f339')}">
    <img data-emoji="🍄" alt=":mushroom:" class="emoji large" src="{$c->getSmileyPath('1f344')}">

    <img data-emoji="🍦" class="emoji large" src="{$c->getSmileyPath('1f366')}">
    <img data-emoji="🍩" class="emoji large" src="{$c->getSmileyPath('1f369')}">
    <img data-emoji="🍪" class="emoji large" src="{$c->getSmileyPath('1f36a')}">
    <img data-emoji="🍫" class="emoji large" src="{$c->getSmileyPath('1f36b')}">
    <img data-emoji="🍰" class="emoji large" src="{$c->getSmileyPath('1f370')}">
    <img data-emoji="🍺" class="emoji large" src="{$c->getSmileyPath('1f37a')}">

    <img data-emoji="🍔" alt=":hamburger:" class="emoji large" src="{$c->getSmileyPath('1f354')}">
    <img data-emoji="🍕" alt=":pizza:" class="emoji large" src="{$c->getSmileyPath('1f355')}">
    <img data-emoji="🍗" alt=":poultry_leg:" class="emoji large" src="{$c->getSmileyPath('1f357')}">
    <img data-emoji="🍚" alt=":rice:" class="emoji large" src="{$c->getSmileyPath('1f35a')}">
    <img data-emoji="🍜" alt=":ramen:" class="emoji large" src="{$c->getSmileyPath('1f35c')}">
    <img data-emoji="🍣" alt=":sushi:" class="emoji large" src="{$c->getSmileyPath('1f363')}">

    <img data-emoji="🛀" alt=":bath:" class="emoji large" src="{$c->getSmileyPath('1f6c0')}">
    <img data-emoji="🎧" alt=":headphones:" class="emoji large" src="{$c->getSmileyPath('1f3a7')}">
    <img data-emoji="🎮" alt=":video_game:" class="emoji large" src="{$c->getSmileyPath('1f3ae')}">
    <img data-emoji="🎫" alt=":ticket:" class="emoji large" src="{$c->getSmileyPath('1f3ab')}">
    <img data-emoji="💼" alt=":briefcase:" class="emoji large" src="{$c->getSmileyPath('1f4bc')}">
    <img data-emoji="🎒" alt=":school_satchel:" class="emoji large" src="{$c->getSmileyPath('1f392')}">

    <img data-emoji="💡" alt=":bulb:" class="emoji large" src="{$c->getSmileyPath('1f4a1')}">
    <img data-emoji="📞" alt=":telephone_receiver:" class="emoji large" src="{$c->getSmileyPath('1f4de')}">
    <img data-emoji="🔥" alt=":fire:" class="emoji large" src="{$c->getSmileyPath('1f525')}">
    <img data-emoji="🕐" alt=":clock1:" class="emoji large" src="{$c->getSmileyPath('1f550')}">
    <img data-emoji="✉" alt=":email:" class="emoji large" src="{$c->getSmileyPath('2709')}">
    <img data-emoji="✏" alt=":pencil2:" class="emoji large" src="{$c->getSmileyPath('270f')}">

    <img data-emoji="💋" alt=":kiss:" class="emoji large" src="{$c->getSmileyPath('1f48b')}">
    <img data-emoji="♥" alt=":hearts:" class="emoji large" src="{$c->getSmileyPath('2665')}">
    <img data-emoji="💊" alt=":pill:" class="emoji large" src="{$c->getSmileyPath('1f48a')}">
    <img data-emoji="💩" alt=":hankey:" class="emoji large" src="{$c->getSmileyPath('1f4a9')}">
    <img data-emoji="☕" alt=":coffee:" class="emoji large" src="{$c->getSmileyPath('2615')}">
    <img data-emoji="⏰" alt=":alarm_clock:" class="emoji large" src="{$c->getSmileyPath('23f0')}">

    <img data-emoji="🚈" class="emoji large" src="{$c->getSmileyPath('1f688')}">
    <img data-emoji="🚋" class="emoji large" src="{$c->getSmileyPath('1f68b')}">
    <img data-emoji="🚌" class="emoji large" src="{$c->getSmileyPath('1f68c')}">
    <img data-emoji="🚐" class="emoji large" src="{$c->getSmileyPath('1f690')}">
    <img data-emoji="🚕" class="emoji large" src="{$c->getSmileyPath('1f695')}">
    <img data-emoji="🚗" class="emoji large" src="{$c->getSmileyPath('1f697')}">

    <img data-emoji="🐰" alt=":rabbit:" class="emoji large" src="{$c->getSmileyPath('1f430')}">
    <img data-emoji="🐨" alt=":koala:" class="emoji large" src="{$c->getSmileyPath('1f428')}">
    <img data-emoji="🐮" alt=":cow:" class="emoji large" src="{$c->getSmileyPath('1f42e')}">
    <img data-emoji="🦊" alt=":fox:" class="emoji large" src="{$c->getSmileyPath('1f98a')}">
    <img data-emoji="🐺" alt=":wolf:" class="emoji large" src="{$c->getSmileyPath('1f43a')}">
    <img data-emoji="🦁" alt=":lion:" class="emoji large" src="{$c->getSmileyPath('1f981')}">

    <img data-emoji="🐷" alt=":pig:" class="emoji large" src="{$c->getSmileyPath('1f437')}">
    <img data-emoji="🐵" alt=":monkey_face:" class="emoji large" src="{$c->getSmileyPath('1f435')}">
    <img data-emoji="🐶" alt=":dog:" class="emoji large" src="{$c->getSmileyPath('1f436')}">
    <img data-emoji="🐸" alt=":frog:" class="emoji large" src="{$c->getSmileyPath('1f438')}">
    <img data-emoji="🐹" alt=":hamster:" class="emoji large" src="{$c->getSmileyPath('1f439')}">
    <img data-emoji="🐻" alt=":bear:" class="emoji large" src="{$c->getSmileyPath('1f43b')}">
</div>
