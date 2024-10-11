{loop="$calls"}
    <li>
        <span class="primary icon gray">
            <i class="material-symbols">interpreter_mode</i>
        </span>
        <div>
            <p>{$value->id}</p>
            <p><i class="material-symbols icon green blink">phone_in_talk</i> {$c->__('visio.in_call')}</p>
        </div>
    </li>
{/loop}