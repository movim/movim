<div class="tabelem" title="{$c->__('page.help')}" id="help_widget">
    <ul class="list thick active">
        <li>
            <span class="primary icon bubble color blue">
                <i class="zmdi zmdi-github-alt"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p>{$c->__('wiki.question')}</p>
            <p><a href="https://github.com/edhelas/movim/wiki" target="_blank">{$c->__('wiki.button')}</a></p>
        </li>
        <li>
            <span class="primary icon bubble color orange">
                <i class="zmdi zmdi-email"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p>{$c->__('ml.question')}</p>
            <p><a href="https://github.com/edhelas/movim/wiki/Mailing-List" target="_blank">{$c->__('ml.button')}</a></p>
        </li>
        <li class="condensed action" onclick="Help_ajaxAddChatroom()">
            <span class="primary icon bubble color green">
                <i class="zmdi zmdi-comment-text-alt"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-accounts-add"></i>
            </span>
            <p>{$c->__('chatroom.question')}</p>
            <p>{$c->__('chatroom.button')}<br/>movim@conference.movim.eu</p>
        </li>
    </ul>
    <!--
    <div class="clear spacetop"></div>

    <h2 class="padded_top_bottom">{$c->__('help.faq')}</h2>

    <div class="card">
        <article>
            <header>
                <ul class="simple">
                    <li><h3>{$c->__('banner.title')}</h3></li>
                </ul>
            </header>
            <section>
                <p>{$c->__('banner.info1')}</p>
                <ul class="thin">
                    <li>
                        <span class="color icon bubble gray small"></span>
                        <span>{$c->__('banner.white')}</span>
                    </li>
                    <li>
                        <span class="color icon bubble green small"></span>
                        <span>{$c->__('banner.green')}</span>
                    </li>
                    <li>
                        <span class="color icon bubble orange small"></span>
                        <span>{$c->__('banner.orange')}</span>
                    </li>
                    <li>
                        <span class="color icon bubble red small"></span>
                        <span>{$c->__('banner.red')}</span>
                    </li>
                    <li>
                        <span class="color icon bubble black small"></span>
                        <span>{$c->__('banner.black')}</span>
                    </li>
                </ul>
            </section>
        </article>

    </div>-->
</div>
