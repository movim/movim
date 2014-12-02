<div class="tabelem paddedtop" title="{$c->__('page.help')}" id="help_widget">
    <h1><i class="fa fa-life-ring"></i> {$c->__('page.help')}</h1>

    <div class="block third">
        <div class="placeholder icon file">
            <p class="paddedtop">{$c->__('wiki.question')}</p>

            <a class="button color blue" href="http://wiki.movim.eu" target="_blank">
                <i class="fa fa-file-text-o"></i> {$c->__('wiki.button')}
            </a>
        </div>
    </div>

    <div class="block third">
        <div class="placeholder icon chat">
            <p class="paddedtop">{$c->__('chatroom.question')}</p>

            <a class="button color green" href="#" onclick="Help.joinChatroom()">
                <i class="fa fa-envelope-o"></i> {$c->__('chatroom.button')}
            </a>
        </div>
    </div>

    <div class="block third ">
        <div class="placeholder icon plane">
            <p class="paddedtop">{$c->__('ml.question')}</p>

            <a class="button color orange" href="http://wiki.movim.eu/en:mailing_list" target="_blank">
                <i class="fa fa-envelope-o"></i> {$c->__('ml.button')}
            </a>
        </div>
    </div>

    <div class="clear spacetop"></div>

    <h1><i class="fa fa-question"></i> {$c->__('help.faq')}</h1>
    
    <div class="block large">
        <h2>{$c->__('banner.title')}</h2>
        <center>    
        <div title="{function="getFlagTitle("white")"}" style="width: 60px; height: 50px; display: inline-block;" class="protect white"></div>
        <div title="{function="getFlagTitle("green")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect green"></div>
        <div title="{function="getFlagTitle("orange")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect orange"></div>
        <div title="{function="getFlagTitle("red")"}"  style="width: 60px; height: 50px; display: inline-block;" class="protect red"></div>
        <div title="{function="getFlagTitle("black")"}" title="{$c->__('Help')}" style="width: 60px; height: 50px; display: inline-block;" class="protect black"></div>
        </center>
            
        <p>{$c->__('banner.info1')}</p>

        <p>
            <ul class="clean">
                <li>{$c->__('banner.white')}</li>
                <li>{$c->__('banner.green')}</li>
                <li>{$c->__('banner.orange')}</li>
                <li>{$c->__('banner.red')}</li>
                <li>{$c->__('banner.black')}</li>
            </ul>
        </p>
    </div>
</div>
