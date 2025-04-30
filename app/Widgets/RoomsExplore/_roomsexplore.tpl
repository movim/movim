<section>
    <ul class="list active">
        <li onclick="RoomsUtils_ajaxAdd(); Drawer.clear();">
            <span class="primary icon gray">
                <i class="material-symbols">input</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('rooms.join_custom')}</p>
            </div>
        </li>
    </ul>
    <hr />
    <ul class="list divided spaced middle" id="roomsexplore_local"></ul>
    <ul class="list divided spaced middle" id="roomsexplore_global"></ul>
</section>
<footer id="roomsexplore_bar">
    <ul class="list">
        <li class="search">
            <form name="search" onsubmit="return false;">
                <div>
                    <input name="keyword" autocomplete="off"
                        title="{$c->__('search.keyword')}"
                        placeholder="{$c->__('rooms.explore_placeholder')}"
                        oninput="RoomsExplore.searchSomething(this.value)"
                        type="text">
                </div>
            </form>
        </li>
    </ul>
</footer>
