<div>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
    <span class="on_desktop icon"><i class="zmdi zmdi-filter-list"></i></span>
    <form>
        <div>
            <div class="select">
                <select onchange="window[this.value].apply()" name="language" id="language">
                    <option value="Menu_ajaxGetAll" selected="selected">{$c->__('menu.all')}</option>
                    <option value="Menu_ajaxGetNews" >{$c->__('menu.groups')}</option>
                    <option value="Menu_ajaxGetFeed" >{$c->__('menu.contacts')}</option>
                </select>
            </div>
        </div>
    </form>
</div>
