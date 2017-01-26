<section id="onboarding" class="public">
    <div class="placeholder icon">
        <h3>{$c->__('onboarding.public_title')}</h3>
        <h4>{$c->__('onboarding.public_text')}</h4>
        <h4>{$c->__('onboarding.public_text_second')}</h4>
    </div>
</section>
<div>
    <a onclick="Dialog_ajaxClear();" class="button flat">
        {$c->__('button.refuse')}
    </a>
    <a onclick="Onboarding_ajaxEnablePublic(); Dialog_ajaxClear();" class="button flat">
        {$c->__('button.accept')}
    </a>
</div>
