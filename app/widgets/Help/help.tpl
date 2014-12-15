<div class="tabelem" title="{$c->__('page.help')}" id="help_widget">
    <ul class="thick active">
        <li class="condensed">
            <a href="http://wiki.movim.eu" target="_blank">
                <span class="icon bubble color blue">
                    <i class="md md-subject"></i>
                </span>
                <span>{$c->__('wiki.question')}</span>
                <p>{$c->__('wiki.button')}</p>
            </a>
        </li>
        <li class="condensed">
            <a onclick="Help.joinChatroom()" target="_blank">
                <span class="icon bubble color green">
                    <i class="md md-chat"></i>
                </span>
                <span>{$c->__('chatroom.question')}</span>
                <p>{$c->__('chatroom.button')}</p>
            </a>
        </li>
        <li class="condensed">
            <a href="http://wiki.movim.eu/en:mailing_list" target="_blank">
                <span class="icon bubble color orange">
                    <i class="md md-email"></i>
                </span>
                <span>{$c->__('ml.question')}</span>
                <p>{$c->__('ml.button')}</p>
            </a>
        </li>
    </ul>

    <div class="clear spacetop"></div>

    <h2 class="padded">{$c->__('help.faq')}</h2>
    
    <div class="card">
        <article>
            <header>
                <ul class="simple">
                    <li><h3>{$c->__('banner.title')}</h3></li>
                </ul>
            </header>
            <!--<center>    
            <div title="{function="getFlagTitle("white")"}" style="width: 60px; height: 50px; display: inline-block;" class="protect white"></div>
            <div title="{function="getFlagTitle("green")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect green"></div>
            <div title="{function="getFlagTitle("orange")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect orange"></div>
            <div title="{function="getFlagTitle("red")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect red"></div>
            <div title="{function="getFlagTitle("black")"}" title="{$c->__('Help')}" style="width: 60px; height: 50px; display: inline-block;" class="protect black"></div>
            </center>-->
            <section>
                <p>{$c->__('banner.info1')}</p>
                <ul class="thin">
                    <li>
                        <span class="color icon bubble brown small"></span>
                        {$c->__('banner.white')}
                    </li>
                    <li>
                        <span class="color icon bubble green small"></span>
                        {$c->__('banner.green')}
                    </li>
                    <li>
                        <span class="color icon bubble orange small"></span>
                        {$c->__('banner.orange')}
                    </li>
                    <li>
                        <span class="color icon bubble red small"></span>
                        {$c->__('banner.red')}
                    </li>
                    <li>
                        <span class="color icon bubble black small"></span>
                        {$c->__('banner.black')}
                    </li>
                </ul>
            </section>
        </article>

    </div>
</div>
