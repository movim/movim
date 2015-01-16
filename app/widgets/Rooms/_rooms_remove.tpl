<section>
    <h3>{$c->__('chatrooms.remove_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('chatrooms.remove_text')}</h4>
    <br />
    <h4 class="gray">{$room}</h4>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="Rooms_ajaxRemove('{$room}'); Dialog.clear()">
        {$c->__('button.remove')}
    </a>
</div>
