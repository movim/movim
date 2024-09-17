<form enctype="multipart/form-data" method="post" action="index.php" name="general" onchange="Config_ajaxSubmit(MovimUtils.formToJson('general'));">
    <div class="block">
        <ul class="list fill flex ">
            <li class="subheader large block"><div><p>{$c->__('config.audio_title')}</p></div></li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">call</i>
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
                <div>
                    <p class="normal line">{$c->__('config.audio_call')}</p>
                </div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">forum</i>
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
                <div>
                    <p class="normal line">{$c->__('config.audio_chat')}</p>
                </div>
            </li>
        </ul>
    </div>

    <ul class="list fill">
        <li class="subheader"><div><p>{$c->__('config.general')}</p></div></li>
    </ul>

    <div class="block">
        <ul class="list fill">
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">language</i>
                </span>
                <div>
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
            </li>
        </ul>
    </div>

    <div class="block">
        <ul class="list middle fill">
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">forum</i>
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
                <div>
                    <p>{$c->__('config.chatmain')}</p>
                    <p class="all">{$c->__('config.chatmain_text')}</p>
                </div>
            </li>

            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">lock</i>
                </span>
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->omemoenabled"}checked{/if}
                            type="checkbox"
                            onchange="setTimeout(() => MovimUtils.reloadThis(), 1000)"
                            id="omemoenabled"
                            name="omemoenabled"/>
                        <label for="omemoenabled"></label>
                    </div>
                </span>
                <div>
                    <p>{$c->__('config.omemoenabled')}</p>
                    <p class="all">{$c->__('config.omemoenabled_text')}</p>
                </div>
            </li>

            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">explicit</i>
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
                <div>
                    <p>{$c->__('config.nsfw')}</p>
                    <p class="all">{$c->__('config.nsfw_text')}</p>
                </div>
            </li>

            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">brightness_2</i>
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
                <div>
                    <p>{$c->__('config.night_mode')}</p>
                    <p class="all">{$c->__('config.night_mode_text')}</p>
                    <span class="night_mode_detected supporting">
                        <i class="material-symbols">night_sight_auto</i>
                        {$c->__('config.night_mode_detected')}
                    </span>
                </div>
            </li>
        </ul>
    </div>
</form>
