<div class="popup" id="bookmarkurladd">
    <form name="bookmarkurladd">
        <fieldset>
            <legend>{$c->t('Add a new URL')}</legend>
            
            <div id="bookmarkadderror"></div>
            <div class="element large mini">
                <input name="url" placeholder="{$c->t('URL')}"/>
            </div>
            <div class="element large mini">
                <input name="name" placeholder="{$c->t('Name')}"/>
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
                onclick="movim_toggle_display('#bookmarkurladd')"
            >
                {$c->t('Close')}
            </a>
        </div>
    </form>
</div>
