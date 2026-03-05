<section>
    <h3>{$c->__('rooms.destroy_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('rooms.destroy_text')}</h4>
    <br />
    <h4 class="gray">{$conference->name ?? $conference->conference}</h4>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="SpaceRooms_ajaxDestroy('{$server}', '{$node}', '{$conference->conference|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.destroy')}
    </button>
</footer>
