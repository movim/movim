<section id="onboarding" class="public">
    <div class="placeholder">
        <h3>{$c->__('onboarding.public_title')}</h3>
        <h4>{$c->__('onboarding.public_text')}</h4>
        <h4>{$c->__('onboarding.public_text_second')}</h4>
    </div>
</section>
<div class="no_bar">
    <button onclick="Onboarding_ajaxEnableRestricted(); Dialog_ajaxClear(); Onboarding.check();" class="button flat">
        {$c->__('button.refuse')}
    </button>
    <button onclick="Onboarding_ajaxEnablePublic(); Dialog_ajaxClear(); Onboarding.check();" class="button flat">
        {$c->__('button.accept')}
    </button>
</div>
