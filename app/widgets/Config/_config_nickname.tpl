<section>
    <h3>{$c->__('profile.info')}</h3>
    <form name="nickname" onsubmit="return false;">
        <div>
            <input name="nickname"
                id="nickname"
                type="text"
                pattern="[A-Za-z0-9-_]+"
                {if="isset($me->nickname)"}
                    value="{$me->nickname}"
                {/if}
                placeholder="John_Bob1337"/>
            <label for="nickname">{$c->__('general.nickname')}</label>
        </div>
    </form>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="Config_ajaxSaveNickname(document.querySelector('form[name=nickname] input#nickname').value)" class="button flat">
        {$c->__('button.save')}
    </button>
</div>
