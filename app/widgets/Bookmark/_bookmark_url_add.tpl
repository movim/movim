<div class="popup" id="bookmarkurladd">
    <form name="bookmarkurladd">
        <fieldset>
            <legend>{$c->__('url.add')}</legend>
            
            <div id="bookmarkadderror"></div>
            <div class="element large mini">
                <input name="url" placeholder="{$c->__('url.url')}"/>
            </div>
            <div class="element large mini">
                <input name="name" placeholder="{$c->__('url.name')}"/>
            </div>
        </fieldset>
        <div class="menu">
            <a 
                class="button icon yes black merged left"
                onclick="{$submit}"
            >
                {$c->__('button.add')}
            </a><a 
                class="button icon no black merged right" 
                onclick="movim_toggle_display('#bookmarkurladd')"
            >
                {$c->__('button.close')}
            </a>
        </div>
    </form>
</div>
