<section id="search">
    {if="$chatroomactions"}
        <br />
        <ul class="list flex active card shadow thick">
            <li class="block color" onclick="RoomsExplore_ajaxSearch();">
                <i class="main material-symbols">chat</i>
                <span class="primary icon">
                    <i class="material-symbols">explore</i>
                </span>
                <div>
                    <p class="line two">
                        {$c->__('rooms.join')}
                    </p>
                    <p></p>
                </div>
            </li>
            <li class="block" onclick="RoomsUtils_ajaxAdd(false, null, true); Drawer.clear()">
                <i class="main material-symbols">star</i>
                <span class="primary icon transparent">
                    <i class="material-symbols">group_add</i>
                </span>
                <div>
                    <p class="line two">
                        {$c->__('rooms.create')}
                    </p>
                    <p></p>
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
            <h4>{$c->__('input.open_me_using')} <span class="chip outline">Ctrl</span> + <span class="chip outline">M</span></h4>
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
