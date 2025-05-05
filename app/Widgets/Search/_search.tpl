<section id="search">
    {if="$chatroomactions"}
        <ul class="list active">
            <li onclick="RoomsExplore_ajaxSearch();">
                <span class="primary icon gray">
                    <i class="material-symbols">explore</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('rooms.join')}
                    </p>
                </div>
            </li>
            <li onclick="RoomsUtils_ajaxAdd(false, null, true); Drawer.clear()">
                <span class="primary icon gray">
                    <i class="material-symbols">group_add</i>
                </span>
                <span class="control icon gray">
                    <i class="material-symbols">chevron_right</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('rooms.create')}
                    </p>
                </div>
            </li>
        </ul>
        <hr />
    {/if}
    <ul id="roster" class="list spin"></ul>
    <ul class="list divided spaced middle" id="roomsexplore_global"></ul>

    <div id="results">
        <div class="placeholder">
            <i class="material-symbols">search</i>
            <h4>{$c->__('search.subtitle')}</h4>
        </div>
    </div>

    <br />
</section>
<footer id="searchbar">
    <ul class="list">
        <li class="search">
            <form name="search" onsubmit="return false;">
                <div>
                    <input name="keyword" autocomplete="off"
                        title="{$c->__('search.keyword')}"
                        placeholder="{$c->__('search.placeholder')}"
                        oninput="Search.searchSomething(this.value)"
                        type="text">
                </div>
            </form>
        </li>
    </ul>
</footer>
