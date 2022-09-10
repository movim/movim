<div class="tabelem padded_top_bottom" title="{$c->__('notificationconfig.title')}" data-mobileicon="notifications" id="notificationconfig_widget">
    <div id="notificationconfig_widget_request"></div>
    <form enctype="multipart/form-data" method="post" action="index.php" name="audioconfig" onchange="NotificationConfig_ajaxAudioSubmit(MovimUtils.formToJson('audioconfig'));">
        <div class="block">
            <ul class="list fill flex ">
                <li class="subheader large block"><div><p>{$c->__('notificationconfig.audio_title')}</p></div></li>
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
                    <div>
                        <p class="normal line">{$c->__('notificationconfig.audio_call')}</p>
                    </div>
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
                    <div>
                        <p class="normal line">{$c->__('notificationconfig.audio_chat')}</p>
                    </div>
                </li>
            </ul>
        </div>
    </form>

    <div id="notificationconfig_widget_push"></div>
</div>