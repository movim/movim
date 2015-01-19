<ul class="active">
    <li onclick="Menu_ajaxRefresh()" title="{$c->__('menu.refresh')}">
        <span class="icon">
            <i class="md md-refresh"></i>
        </span>
    </li>
</ul>
<span id="menu" class="on_mobile icon" onclick="MovimTpl.toggleMenu()"><i class="md md-menu"></i></span>
<span class="on_desktop icon"><i class="md md-speaker-notes"></i></span>
<form>
    <div>
        <div class="select">
            <select onchange="window[this.value].apply()" name="language" id="language">
                <option value="Menu_ajaxGetAll" selected="selected">{$c->__('menu.all')}</option>
                <option value="Menu_ajaxGetNews" >{$c->__('menu.news')}</option>
                <option value="Menu_ajaxGetFeed" >{$c->__('menu.contacts')}</option>
            </select>
        </div>
    </div>
</form>
