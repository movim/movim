<section id="onboarding" class="notifications">
    <div class="placeholder icon">
        <h3>{$c->__('onboarding.notifications_title')}</h3>
        <h4>{$c->__('onboarding.notifications_text')}</h4>
        <h4>{$c->__('onboarding.notifications_text_second')}</h4>
    </div>
</section>
<div>
    <button onclick="Dialog_ajaxClear(); Onboarding.check();" class="button flat">
        {$c->__('button.not_now')}
    </button>
    <button onclick="Onboarding.enableNotifications(); Dialog_ajaxClear(); Onboarding.check();" class="button flat">
        {$c->__('button.enable')}
    </button>
</div>
