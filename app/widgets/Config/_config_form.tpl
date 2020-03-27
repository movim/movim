<form enctype="multipart/form-data" method="post" action="index.php" name="general">
    <br/>
    <h3>{$c->__('config.general')}</h3>

    <div class="block">
        <div class="select">
            <select name="language" id="language" value="{$conf->language}">
                <option value="en">English (default)</option>
                    {loop="$languages"}
                        {if="$key == $conf->language"}
                            <option
                                value="{$key}"
                                dir="auto"
                                selected="selected">
                                {$value}
                            </option>
                        {else}
                            <option
                                dir="auto"
                                value="{$key}">
                                {$value}
                            </option>
                        {/if}
                    {/loop}
            </select>
        </div>
        <label for="language">{$c->__('config.language')}</label>
    </div>

    <div class="block">
        <ul class="list thick">
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">forum</i>
                </span>
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->chatmain"}checked{/if}
                            type="checkbox"
                            id="chatmain"
                            name="chatmain"/>
                        <label for="chatmain"></label>
                    </div>
                </span>
                <p>{$c->__('config.chatmain')}</p>
                <p class="all">{$c->__('config.chatmain_text')}</p>
            </li>
        </ul>
    </div>

    <div class="block">
        <ul class="list thick">
            <li>
                <span class="primary icon gray">
                    18+
                </span>
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->nsfw"}checked{/if}
                            type="checkbox"
                            id="nsfw"
                            name="nsfw"/>
                        <label for="nsfw"></label>
                    </div>
                </span>
                <p>{$c->__('config.nsfw')}</p>
                <p class="all">{$c->__('config.nsfw_text')}</p>
            </li>
        </ul>
    </div>

    <div class="block">
        <ul class="list thick">
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">brightness_2</i>
                </span>
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->nightmode"}checked{/if}
                            type="checkbox"
                            id="nightmode"
                            onchange="Config.switchNightMode()"
                            name="nightmode"/>
                        <label for="nightmode"></label>
                    </div>
                </span>
                <p>{$c->__('config.night_mode')}</p>
                <p class="all">{$c->__('config.night_mode_text')}</p>
            </li>
        </ul>
    </div>
    <br/>
    <h3>{$c->__('config.notification_title')}</h3>

    <div class="block">
        <ul class="list middle">
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">call</i>
                </span>
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->notificationcall"}checked{/if}
                            type="checkbox"
                            id="notificationcall"
                            name="notificationcall"/>
                        <label for="notificationcall"></label>
                    </div>
                </span>
                <p class="normal line">{$c->__('config.notification_call')}</p>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">forum</i>
                </span>
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->notificationchat"}checked{/if}
                            type="checkbox"
                            id="notificationchat"
                            name="notificationchat"/>
                        <label for="notificationchat"></label>
                    </div>
                </span>
                <p class="normal line">{$c->__('config.notification_chat')}</p>
            </li>
        </ul>
    </div>

    <div class="clear padded"></div>
    <button
        type="button"
        onclick="{$submit}"
        class="button color oppose" >
        {$c->__('button.save')}
    </button>
    <div class="clear"></div>
</form>
