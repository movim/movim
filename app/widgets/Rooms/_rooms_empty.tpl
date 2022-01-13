<ul class="list thick spaced empty">
    <li>
        <span class="primary icon green">
            <i class="material-icons">people_outline</i>
        </span>
        <div>
            <p>{$c->__('rooms.empty_text1')}</p>
            <p>{$c->__('rooms.empty_text2')}</p>
        </div>
    </li>
    {if="!$c->getUser()->hasBookmarksConvertion()"}
        <li>
            <span class="primary icon purple">
                <i class="material-icons">help</i>
            </span>
            <span class="control icon active" onclick="RoomsUtils_ajaxSyncBookmark()">
                <i class="material-icons">sync</i>
            </span>
            <div>
                <p>{$c->__('rooms.empty_synchronize_title')}</p>
                <p>{$c->__('rooms.empty_synchronize_text')}</p>
            </div>
        </li>
    {/if}
</ul>