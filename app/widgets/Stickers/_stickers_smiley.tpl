<section>
    <ul class="tabs">
        <li onclick="Stickers_ajaxShow('{$jid}')">
            <a href="#"><img alt=":sticker:" class="emoji medium" src="{$icon}"></a>
        </li>
        <li onclick="Stickers_ajaxSmiley('{$jid}')" class="active">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
        <li onclick="Stickers_ajaxSmileyTwo('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f44d')}"></a>
        </li>
    </ul>
    <table class="emojis">
        <tbody>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😀"><img class="emoji large" src="{$c->getSmileyPath('1f600')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😁"><img class="emoji large" src="{$c->getSmileyPath('1f601')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😂"><img class="emoji large" src="{$c->getSmileyPath('1f602')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😃"><img class="emoji large" src="{$c->getSmileyPath('1f603')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😄"><img class="emoji large" src="{$c->getSmileyPath('1f604')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😅"><img class="emoji large" src="{$c->getSmileyPath('1f605')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😆"><img class="emoji large" src="{$c->getSmileyPath('1f606')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😇"><img class="emoji large" src="{$c->getSmileyPath('1f607')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😈"><img class="emoji large" src="{$c->getSmileyPath('1f608')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😉"><img class="emoji large" src="{$c->getSmileyPath('1f609')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😊"><img class="emoji large" src="{$c->getSmileyPath('1f60a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😋"><img class="emoji large" src="{$c->getSmileyPath('1f60b')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😌"><img class="emoji large" src="{$c->getSmileyPath('1f60c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😍"><img class="emoji large" src="{$c->getSmileyPath('1f60d')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😎"><img class="emoji large" src="{$c->getSmileyPath('1f60e')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😏"><img class="emoji large" src="{$c->getSmileyPath('1f60f')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😐"><img class="emoji large" src="{$c->getSmileyPath('1f610')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😑"><img class="emoji large" src="{$c->getSmileyPath('1f611')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😒"><img class="emoji large" src="{$c->getSmileyPath('1f612')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😓"><img class="emoji large" src="{$c->getSmileyPath('1f613')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😔"><img class="emoji large" src="{$c->getSmileyPath('1f614')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😕"><img class="emoji large" src="{$c->getSmileyPath('1f615')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😖"><img class="emoji large" src="{$c->getSmileyPath('1f616')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😗"><img class="emoji large" src="{$c->getSmileyPath('1f617')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😘"><img class="emoji large" src="{$c->getSmileyPath('1f618')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😙"><img class="emoji large" src="{$c->getSmileyPath('1f619')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😚"><img class="emoji large" src="{$c->getSmileyPath('1f61a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😛"><img class="emoji large" src="{$c->getSmileyPath('1f61b')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😜"><img class="emoji large" src="{$c->getSmileyPath('1f61c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😝"><img class="emoji large" src="{$c->getSmileyPath('1f61d')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😞"><img class="emoji large" src="{$c->getSmileyPath('1f61e')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😟"><img class="emoji large" src="{$c->getSmileyPath('1f61f')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😠"><img class="emoji large" src="{$c->getSmileyPath('1f620')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😡"><img class="emoji large" src="{$c->getSmileyPath('1f621')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😢"><img class="emoji large" src="{$c->getSmileyPath('1f622')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😣"><img class="emoji large" src="{$c->getSmileyPath('1f623')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😤"><img class="emoji large" src="{$c->getSmileyPath('1f624')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😥"><img class="emoji large" src="{$c->getSmileyPath('1f625')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😦"><img class="emoji large" src="{$c->getSmileyPath('1f626')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😧"><img class="emoji large" src="{$c->getSmileyPath('1f627')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😨"><img class="emoji large" src="{$c->getSmileyPath('1f628')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😩"><img class="emoji large" src="{$c->getSmileyPath('1f629')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😪"><img class="emoji large" src="{$c->getSmileyPath('1f62a')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😫"><img class="emoji large" src="{$c->getSmileyPath('1f62b')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😬"><img class="emoji large" src="{$c->getSmileyPath('1f62c')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😭"><img class="emoji large" src="{$c->getSmileyPath('1f62d')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😮"><img class="emoji large" src="{$c->getSmileyPath('1f62e')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😯"><img class="emoji large" src="{$c->getSmileyPath('1f62f')}"></td>
            </tr>
            <tr class="active">
                <td onclick="Stickers.addSmiley(this);" data-emoji="😰"><img class="emoji large" src="{$c->getSmileyPath('1f630')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😱"><img class="emoji large" src="{$c->getSmileyPath('1f631')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😲"><img class="emoji large" src="{$c->getSmileyPath('1f632')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😳"><img class="emoji large" src="{$c->getSmileyPath('1f633')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😴"><img class="emoji large" src="{$c->getSmileyPath('1f634')}"></td>
                <td onclick="Stickers.addSmiley(this);" data-emoji="😵"><img class="emoji large" src="{$c->getSmileyPath('1f635')}"></td>
            </tr>
        </tbody>
    </table>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
