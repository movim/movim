<section>
    <h3>{$c->__('notification.request_title')}</h3>

    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">notifications_active</i>
            </span>
            <div>
                <p>{$c->__('notification.request_info')}</p>
                <p>{$c->__('notification.request_info2')}</p>
            </div>
        </li>
    </ul>
</section>
<div class="no_bar">
    <button
        name="submit"
        class="button flat"
        onclick="Notification.request(); Dialog_ajaxClear()">
        {$c->__('notification.request_button')}
    </button>
</div>
