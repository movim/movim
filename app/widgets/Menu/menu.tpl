<div id="menu_widget_wrapper">
    <header>
        <ul class="list">
            <li>
                <span id="menu" class="primary on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
                <span class="primary on_desktop icon gray"><i class="zmdi zmdi-filter-list"></i></span>
                <p>{$c->__('page.news')}</p>
            </li>
            <li>
                <ul class="tabs">
                    <li {if="$type == 'all'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetAll()">{$c->__('menu.all')}</a></li>
                    <li {if="$type == 'news'"}class="active"{/if} ><a href="#" onclick="Menu_ajaxGetNews()"><i class="zmdi zmdi-pages"></i></a></li>
                    <li {if="$type == 'feed'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetFeed()"><i class="zmdi zmdi-accounts"></i></a></li>
                    <li {if="$type == 'me'"}class="active"{/if}><a href="#" onclick="Menu_ajaxGetMe()"><i class="zmdi zmdi-portable-wifi"></i></a></li>
                </ul>
            </li>
        </ul>
    </header>

    <div id="menu_widget">
        {$c->prepareList('all')}
    </div>
</div>
