<section id="search">
    {if="$chatroomactions"}
        <ul class="list active">
            <li onclick="RoomsExplore_ajaxSearch();">
                <span class="primary icon gray">
                    <i class="material-icons">explore</i>
                </span>
                <span class="control icon gray">
                    <i class="material-icons">chevron_right</i>
                </span>
                <div>
                    <p class="line normal">
                        {$c->__('rooms.join')}
                    </p>
                </div>
            </li>
            <li onclick="RoomsUtils_ajaxAdd(false, null, true); Drawer.clear()">
                <span class="primary icon gray">
                    <i class="material-icons">group_add</i>
                </span>
                <span class="control icon gray">
                    <i class="material-icons">chevron_right</i>
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

    <div id="results">
        {autoescape="off"}{$empty}{/autoescape}
    </div>

    <br />
</section>
<div id="searchbar">
    <ul class="list fill">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">search</i>
            </span>
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
</div>
