<ul class="list card thick shadow active">
    <li class="block large" onclick="Menu_ajaxHttpGetAll(); Notification_ajaxClear('news');">
        <span class="primary icon"><i class="material-icons">refresh</i></span>
        <div>
            <p>{$c->__('button.refresh')}</p>
            <p class="line">{$c->__('post.new_items', $count)}</p>
        </div>
    </li>
</ul>
