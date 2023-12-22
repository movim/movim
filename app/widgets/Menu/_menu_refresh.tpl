<ul class="list card thick shadow active">
    <li class="block large" onclick="Menu_ajaxHttpGetAll(); Notif_ajaxClear('news');">
        <span class="primary icon"><i class="material-symbols">refresh</i></span>
        <div>
            <p>{$c->__('button.refresh')}</p>
            <p class="line">{$c->__('post.new_items', $count)}</p>
        </div>
    </li>
</ul>
