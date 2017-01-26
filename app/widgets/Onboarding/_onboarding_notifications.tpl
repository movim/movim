<section id="onboarding" class="notifications">
    <div class="placeholder icon">
        <h3>{$c->__('onboarding.notifications_title')}</h3>
        <h4>{$c->__('onboarding.notifications_text')}</h4>
        <h4>{$c->__('onboarding.notifications_text_second')}</h4>
    </div>
</section>
<div>
    <a onclick="Onboarding.disableNotifications();  Dialog_ajaxClear();" class="button flat">
        {$c->__('button.not_now')}
    </a>
    <a onclick="Onboarding.enableNotifications(); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.enable')}
    </a>
</div>
