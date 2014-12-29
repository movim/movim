<header id="header">
    <span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
    <span class="on_desktop icon"><i class="md md-speaker-notes"></i></span>
    <form>
        <div>
            <div class="select">
                <select name="language" id="language">
                    <option onclick="Menu_ajaxGetAll()" selected="selected" value="all">All **FIXME*</option>
                    <option onclick="Menu_ajaxGetNews()" value="news">News</option>
                    <option onclick="Menu_ajaxGetFeed()" value="contacts">Contacts</option>
                </select>
            </div>
        </div>
    </form>
    <!--<h2>{$c->__('page.news')}</h2>-->
</header>
