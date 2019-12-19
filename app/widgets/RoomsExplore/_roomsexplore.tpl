<section>
    <ul class="list divided spaced middle" id="roomsexplore_local"></ul>
    <ul class="list divided spaced middle" id="roomsexplore_global"></ul>
</section>
<div id="roomsexplore_bar">
    <ul class="list">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">search</i>
            </span>
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
</div>
