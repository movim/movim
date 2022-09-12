{if="$pushSubscriptions->count() > 0"}
<form>
    <div>
        <ul class="list fill card">
            <li class="subheader">
                <div>
                    <p>{$c->__('notificationconfig.push_subscriptions')}</p>
                </div>
            </li>
            <li>
                <div>
                    <p></p>
                    <p>{$c->__('notificationconfig.push_subscriptions_text')}</p>
                </div>
            </li>
            <br />
            {loop="$pushSubscriptions"}
                <li>
                    <span class="primary icon {if="$value->self"}green{else}gray{/if}">
                        <i class="material-icons">notifications</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                data-identifier="{$value->id}"
                                id="pushsubscription_state_{$value->id}"
                                name="pushsubscription_state_{$value->id}"
                                {if="$value->enabled"}checked{/if}
                                onchange="NotificationConfig_ajaxTogglePushConfig({$value->id}, this.checked)"/>
                            <label for="pushsubscription_state_{$value->id}"></label>
                        </div>
                    </span>
                    <div>
                        <p class="normal">
                            <span class="info">{$c->__('omemo.last_activity')}: {$value->updated_at|strtotime|prepareDate}</span>
                            {$value->browser ?? $c->__('notificationconfig.unknown_browser')}
                        </p>
                        <p>{$value->platform ?? $c->__('notificationconfig.unknown_platform')} - {$value->created_at|strtotime|prepareDate}</p>
                    </div>
                </li>
                {if="$value->self"}<br /><hr /><br />{/if}
            {/loop}
        </ul>
    </div>
</form>
{/if}