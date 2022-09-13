<form enctype="multipart/form-data" method="post" action="index.php" name="general" onchange="Config_ajaxSubmit(MovimUtils.formToJson('general'));">
    <ul class="list fill">
        <li class="subheader"><div><p>{$c->__('config.general')}</p></div></li>
    </ul>

    <ul class="list fill middle active">
        <li onclick="Config_ajaxEditNickname()">
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <span class="primary icon gray">
                <i class="material-icons">account_circle</i>
            </span>
            <div>
                <p>{$c->__('profile.info')}</p>
                <p class="all">{$c->__('profile.nickname_info')}</p>
                {if="!empty($conf->nickname)"}
                    <p>{$c->__('profile.nickname_set', $conf->nickname)}</p>
                {/if}
            </div>
        </li>
        <br />
    </ul>


    <div class="block">
        <ul class="list fill">
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">language</i>
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
                <div>
                    <p>{$c->__('config.chatmain')}</p>
                    <p class="all">{$c->__('config.chatmain_text')}</p>
                </div>
            </li>

            <li>
                <span class="primary icon gray">
                    <i class="material-icons">explicit</i>
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
                <div>
                    <p>{$c->__('config.night_mode')}</p>
                    <p class="all">{$c->__('config.night_mode_text')}</p>
                </div>
            </li>
        </ul>
    </div>
</form>
