<ul class="list thick">
    <li>
        <span class="primary icon gray">
            <i class="material-symbols">notifications_active</i>
        </span>
        <div>
            <button
                name="submit"
                class="button oppose green"
                onclick="Notif.request(); Dialog_ajaxClear()">
                {$c->__('notification.request_button')}
            </button>
            <p>{$c->__('notification.request_info')}</p>
            <p>{$c->__('notification.request_info2')}</p>
        </div>
    </li>
</ul>
