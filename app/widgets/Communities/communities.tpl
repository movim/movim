<br />
<ul class="list flex middle active card">
    <li class="block large" onclick="MovimUtils.redirect('{$c->route('explore', 'servers')}')">
        <span class="primary icon">
            <i class="material-icons">view_lists</i>
        </span>
        <span class="control icon">
            <i class="material-icons">chevron_right</i>
        </span>
        <div>
            <p>{$c->__('communities.servers')}</p>
            <p>{$c->__('communities.servers_text')}</p>
        </div>
    </li>
</ul>
<div id="communities" class="spin"></div>
