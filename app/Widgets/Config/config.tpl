<div class="tabelem" title="{$c->__('page.configuration')}" data-mobileicon="settings" id="config_widget">
    <div id="config_widget_form">{autoescape="off"}{$form}{/autoescape}</div>
    <div id="config_widget_mam"></div>
    <ul class="list middle">
        <li class="subheader">
            <div>
                <p>{$c->__('config.confidentiality')}</p>
            </div>
        </li>
        <li>
            <span class="primary icon gray">
                <i class="material-symbols">public</i>
            </span>
            <span class="control">
                <form>
                    <div class="control action">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                id="public"
                                name="public"
                                {if="$me->public"}
                                    checked
                                {/if}
                                onchange="Config_ajaxChangePrivacy(this.checked)">
                            <label for="public"></label>
                        </div>
                    </div>
                </form>
            </span>
            <div>
                <p>{$c->__('profile.privacy_question')}</p>
                <p class="all">{$c->__('profile.privacy_info')}</p>
            </div>
        </li>
        <br />
    </ul>
    <ul class="list middle active">
        <li onclick="Config_ajaxEditNickna$c->me">
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <span class="primary icon gray">
                <i class="material-symbols">account_circle</i>
            </span>
            <div>
                <p>{$c->__('profile.info')}</p>
                <p class="all">{$c->__('profile.nickname_info')}</p>
                {if="!empty($configuration->nickname)"}
                    <p>{$c->__('profile.nickname_set', $configuration->nickname)}</p>
                {/if}
            </div>
        </li>
        <br />
    </ul>

    <div id="config_widget_blog"></div>
</div>
