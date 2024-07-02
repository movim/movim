<section>
    <h3>{$c->__('rooms.destroy_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('rooms.destroy_text')}</h4>
    <br />
    <h4 class="gray">{$room}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="RoomsUtils_ajaxDestroy('{$room}'); Dialog_ajaxClear()">
        {$c->__('button.destroy')}
    </button>
</div>
