<section>
    <ul class="tabs">
        <li onclick="Stickers_ajaxShow('{$jid}')">
            <a href="#"><img alt=":sticker:" class="emoji medium" src="{$icon}"></a>
        </li>
        <li onclick="Stickers_ajaxSmiley('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
        <li onclick="Stickers_ajaxSmileyTwo('{$jid}')" class="active">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f44d')}"></a>
        </li>
    </ul>
    <table class="emojis">
        <tbody>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="👊"><img class="emoji large" src="{$c->getSmileyPath('1f44a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="👋"><img class="emoji large" src="{$c->getSmileyPath('1f44b')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="👌"><img class="emoji large" src="{$c->getSmileyPath('1f44c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="👍"><img class="emoji large" src="{$c->getSmileyPath('1f44d')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="👎"><img class="emoji large" src="{$c->getSmileyPath('1f44e')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="👏"><img class="emoji large" src="{$c->getSmileyPath('1f44f')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍌"><img alt=":banana:" class="emoji large" src="{$c->getSmileyPath('1f34c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍎"><img alt=":apple:" class="emoji large" src="{$c->getSmileyPath('1f34e')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🌼"><img alt=":blossom:" class="emoji large" src="{$c->getSmileyPath('1f33c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🌵"><img alt=":cactus:" class="emoji large" src="{$c->getSmileyPath('1f335')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🌹"><img alt=":rose:" class="emoji large" src="{$c->getSmileyPath('1f339')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍄"><img alt=":mushroom:" class="emoji large" src="{$c->getSmileyPath('1f344')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍦"><img class="emoji large" src="{$c->getSmileyPath('1f366')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍩"><img class="emoji large" src="{$c->getSmileyPath('1f369')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍪"><img class="emoji large" src="{$c->getSmileyPath('1f36a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍫"><img class="emoji large" src="{$c->getSmileyPath('1f36b')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍰"><img class="emoji large" src="{$c->getSmileyPath('1f370')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍺"><img class="emoji large" src="{$c->getSmileyPath('1f37a')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍔"><img alt=":hamburger:" class="emoji large" src="{$c->getSmileyPath('1f354')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍕"><img alt=":pizza:" class="emoji large" src="{$c->getSmileyPath('1f355')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍗"><img alt=":poultry_leg:" class="emoji large" src="{$c->getSmileyPath('1f357')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍚"><img alt=":rice:" class="emoji large" src="{$c->getSmileyPath('1f35a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍜"><img alt=":ramen:" class="emoji large" src="{$c->getSmileyPath('1f35c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🍣"><img alt=":sushi:" class="emoji large" src="{$c->getSmileyPath('1f363')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="🛀"><img alt=":bath:" class="emoji large" src="{$c->getSmileyPath('1f6c0')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🎧"><img alt=":headphones:" class="emoji large" src="{$c->getSmileyPath('1f3a7')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🎮"><img alt=":video_game:" class="emoji large" src="{$c->getSmileyPath('1f3ae')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🎫"><img alt=":ticket:" class="emoji large" src="{$c->getSmileyPath('1f3ab')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="💼"><img alt=":briefcase:" class="emoji large" src="{$c->getSmileyPath('1f4bc')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🎒"><img alt=":school_satchel:" class="emoji large" src="{$c->getSmileyPath('1f392')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="💡"><img alt=":bulb:" class="emoji large" src="{$c->getSmileyPath('1f4a1')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="📞"><img alt=":telephone_receiver:" class="emoji large" src="{$c->getSmileyPath('1f4de')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🔥"><img alt=":fire:" class="emoji large" src="{$c->getSmileyPath('1f525')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🕐"><img alt=":clock1:" class="emoji large" src="{$c->getSmileyPath('1f550')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="✉"><img alt=":email:" class="emoji large" src="{$c->getSmileyPath('2709')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="✏"><img alt=":pencil2:" class="emoji large" src="{$c->getSmileyPath('270f')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="💋"><img alt=":kiss:" class="emoji large" src="{$c->getSmileyPath('1f48b')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="♥"><img alt=":hearts:" class="emoji large" src="{$c->getSmileyPath('2665')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="💊"><img alt=":pill:" class="emoji large" src="{$c->getSmileyPath('1f48a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="💩"><img alt=":hankey:" class="emoji large" src="{$c->getSmileyPath('1f4a9')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="☕"><img alt=":coffee:" class="emoji large" src="{$c->getSmileyPath('2615')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="⏰"><img alt=":alarm_clock:" class="emoji large" src="{$c->getSmileyPath('23f0')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="🚈"><img class="emoji large" src="{$c->getSmileyPath('1f688')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🚋"><img class="emoji large" src="{$c->getSmileyPath('1f68b')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🚌"><img class="emoji large" src="{$c->getSmileyPath('1f68c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🚐"><img class="emoji large" src="{$c->getSmileyPath('1f690')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🚕"><img class="emoji large" src="{$c->getSmileyPath('1f695')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🚗"><img class="emoji large" src="{$c->getSmileyPath('1f697')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="🐷"><img alt=":pig:" class="emoji large" src="{$c->getSmileyPath('1f437')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🐵"><img alt=":monkey_face:" class="emoji large" src="{$c->getSmileyPath('1f435')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🐶"><img alt=":dog:" class="emoji large" src="{$c->getSmileyPath('1f436')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🐸"><img alt=":frog:" class="emoji large" src="{$c->getSmileyPath('1f438')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🐹"><img alt=":hamster:" class="emoji large" src="{$c->getSmileyPath('1f439')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="🐻"><img alt=":bear:" class="emoji large" src="{$c->getSmileyPath('1f43b')}"></td>
            </tr>
        </tbody>
    </table>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
