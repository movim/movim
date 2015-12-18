<ul class="list card thin shadow flex stacked active">
    <li class="block" onclick="{$refresh} Notification_ajaxClear('news');">
        <span class="primary icon"><i class="zmdi zmdi-refresh-sync"></i></span>
        <p>{$c->__('button.refresh')}</p>
        <p>{$c->__('post.new_items', $count)}</p>
    </li>
</ul>
