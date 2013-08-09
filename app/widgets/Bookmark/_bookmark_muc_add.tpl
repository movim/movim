<div class="popup" id="bookmarkmucadd">
    <form name="bookmarkmucadd">
        <fieldset>
            <legend>{$c->t('Add a new Chat Room')}</legend>
            
            <div id="bookmarkmucadderror"></div>
            <div class="element large mini">
                <input name="jid" placeholder="{$c->t('Chat Room ID')}"/>
            </div>
            <div class="element large mini">
                <input name="name" placeholder="{$c->t('Name')}"/>
            </div>
            <div class="element large mini">
                <input name="nick" placeholder="{$c->t('Nickname')}"/>
            </div>
            <div class="element large mini">
                <label>{$c->t('Do you want do join automaticaly this Chat Room ?')}</label>
                <div class="checkbox">
                    <input type="checkbox" id="autojoin" name="autojoin"/>
                    <label for="autojoin"></label>
                </div>
            </div>
        </fieldset>
        <div class="menu">
            <a 
                class="button icon yes black merged left"
                onclick="{$submit}"
            >
                {$c->t('Add')}
            </a><a 
                class="button icon no black merged right" 
                onclick="movim_toggle_display('#bookmarkmucadd')"
            >
                {$c->t('Close')}
            </a>
        </div>
    </form>
</div>
