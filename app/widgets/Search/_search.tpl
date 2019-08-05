<section id="search">
    <ul id="roster" class="list spin"></ul>

    <div id="results">
        {autoescape="off"}{$empty}{/autoescape}
    </div>

    <br />
</section>
<div id="searchbar">
    <ul class="list">
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
