<div>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="md md-menu"></i></span>
    <span class="on_desktop icon"><i class="md md-view-list"></i></span>
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
</div>
<div>
    <span class="icon">
        <i class="md md-edit"></i>
    </span>
    <h2 class="r2">New Post</h2>
    <ul class="active">
        <li onclick="Post_ajaxHelp()">
            <span class="icon">
                <i class="md md-help"></i>
            </span>
        </li>
        <li onclick="Post_ajaxPreview(movim_form_to_json('post'))">
            <span class="icon">
                <i class="md md-remove-red-eye"></i>
            </span>
        </li>
    </ul>
</div>
