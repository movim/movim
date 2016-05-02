<section id="search">
    <ul class="list">
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-search"></i>
            </span>
            <form name="search" onsubmit="return false;">
                <div>
                    <input name="keyword" placeholder="{$c->__('search.keyword')}" onkeyup="Search_ajaxSearch(this.value);" type="text">
                </div>
            </form>
        </li>
    </ul>
    <div id="results">{$empty}</div>
</section>
<div>
    <a onclick="Dialog.clear(); Upload.xhr.abort();" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
