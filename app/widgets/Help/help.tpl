<div class="tabelem" title="{$c->__('page.help')}" id="help_widget">
    <ul class="list thick block divided">
        <li class="subheader">
            <p>{$c->__('apps.question')}</p>
        </li>
        <li class="block">
            <span class="primary icon bubble color green">
                <i class="zmdi zmdi-android"></i>
            </span>
            <p>{$c->__('apps.phone')}<p>
            <p class="all">
                {$c->__('apps.android')}
                <br />
                <a class="button flat" href="https://play.google.com/store/apps/details?id=com.movim.movim" target="_blank">
                    <i class="zmdi zmdi-google-play"></i> Play Store
                </a>
                <a class="button flat" href="https://f-droid.org/packages/com.movim.movim/" target="_blank">
                    <i class="zmdi zmdi-android-alt"></i> F-Droid
                </a>
                <br />
                {$c->__('apps.recommend')} Conversations
                <br />
                <a class="button flat" href="https://play.google.com/store/apps/details?id=eu.siacs.conversations" target="_blank">
                    <i class="zmdi zmdi-google-play"></i> Play Store
                </a>
                <a class="button flat" href="https://f-droid.org/packages/eu.siacs.conversations/" target="_blank">
                    <i class="zmdi zmdi-android-alt"></i> F-Droid
                </a>
            </p>
        </li>
        <li class="block">
            <span class="primary icon bubble color purple">
                <i class="zmdi zmdi-desktop-windows"></i>
            </span>
            <p>{$c->__('apps.computer')}<p>
            <p class="all">
                <a href="https://movim.eu/#apps" target="_blank">
                    {$c->__('apps.computer_text')}
                </a>
            </p>
        </li>
        <li class="subheader">
            <p>{$c->__('page.help')}</p>
        </li>
        <li class="block">
            <span class="primary icon bubble color blue">
                <i class="zmdi zmdi-github-alt"></i>
            </span>
            <p>
                {$c->__('wiki.question')}
            </p>
            <p>
                <a href="https://github.com/edhelas/movim/wiki" target="_blank">
                    {$c->__('wiki.button')}
                </a>
            </p>
        </li>
        <li class="block">
            <span class="primary icon bubble color orange">
                <i class="zmdi zmdi-email"></i>
            </span>
            <p>
                {$c->__('ml.question')}
            </p>
            <p>
                <a href="https://github.com/edhelas/movim/wiki/Mailing-List" target="_blank">
                    {$c->__('ml.button')}
                </a>
            </p>
        </li>
        <li class="block">
            <span class="primary icon bubble color teal">
                <i class="zmdi zmdi-comment-text-alt"></i>
            </span>
            <p>{$c->__('chatroom.question')}</p>
            <p class="all">
                <a href="#" onclick="Help_ajaxAddChatroom()">
                    {$c->__('chatroom.button')} movim@conference.movim.eu
                </a>
            </p>
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
