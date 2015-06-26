<ul class="thick active">
    <li class="condensed" onclick="{$refresh} Notification_ajaxClear('news');">
        <span class="icon"><i class="zmdi zmdi-loop"></i></span>
        <span> {$c->__('button.refresh')}</span>
        <p>{$c->__('post.new_items', $count)}</p>
    </li>
</ul>
