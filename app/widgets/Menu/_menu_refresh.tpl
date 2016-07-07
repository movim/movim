<ul class="list card thin shadow flex stacked active">
    <li class="block large" onclick="{$refresh} Notification_ajaxClear('news');">
        <span class="primary icon"><i class="zmdi zmdi-refresh-sync"></i></span>
        <p>{$c->__('button.refresh')}</p>
        <p class="line">{$c->__('post.new_items', $count)}</p>
    </li>
</ul>
