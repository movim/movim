<section>
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">lock</i>
            </span>
            <div>
                <p>{$c->__('spacesmenu.locked_title')}</p>
                <p>{$c->__('spacesmenu.locked_text')}</p>
            </div>
        </li>
    </ul>
    <ul class="list active middle divided">
        <li id="locked_try_again" onclick="this.classList.add('disabled'); SpacesMenu_ajaxJoin('{$server}', '{$node}')">
            <span class="primary icon gray">
                <i class="material-symbols">replay</i>
            </span>
            <span class="control icon">
                <i class="material-symbols">chevron_forward</i>
            </span>
            <div>
                <p class="line">{$c->__('spacesmenu.locked_try_again')}</p>
            </div>
        </li>
        <li onclick="SpacesMenu_ajaxLeaveMenu('{$server}', '{$node}')">
            <span class="primary icon gray">
                <i class="material-symbols">door_open</i>
            </span>
            <span class="control icon">
                <i class="material-symbols">chevron_forward</i>
            </span>
            <div>
                <p class="line">{$c->__('spaceinfo.leave_title')}</p>
            </div>
        </li>
    </ul>
</section>
<footer>
    <button class="button flat" onclick="Dialog_ajaxClear()">
        {$c->__('button.cancel')}
    </button>
</footer>
