<section>
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">door_open</i>
            </span>
            <div>
                <p>{$c->__('spacesmenu.leave_title')}</p>
                <p>{$c->__('spacesmenu.leave_text')}</p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
    <button
        class="button flat"
        onclick="SpacesMenu_ajaxLeave('{$server}', '{$node}'); Dialog_ajaxClear()"
        >
        {$c->__('button.leave')}
    </button>
</footer>
