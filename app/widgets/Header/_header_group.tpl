<div>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="md md-menu"></i></span>
    <span class="on_desktop icon"><i class="md md-receipt"></i></span>
    <form>
        <div>
            <div class="select">
                <select onchange="window[this.value].apply()" name="language" id="language">
                    <option value="Groups_ajaxSubscriptions" selected="selected">{$c->__('menu.subscriptions')}</option>
                </select>
            </div>
        </div>
    </form>
</div>
