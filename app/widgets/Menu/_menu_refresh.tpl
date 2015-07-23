<ul class="card thin shadow flex stacked active">
    <li class="block condensed" onclick="{$refresh} Notification_ajaxClear('news');">
        <span class="icon"><i class="zmdi zmdi-refresh-sync"></i></span>
        <span> {$c->__('button.refresh')}</span>
        <p>{$c->__('post.new_items', $count)}</p>
    </li>
</ul>
