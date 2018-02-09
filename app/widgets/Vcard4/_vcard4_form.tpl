<ul class="list middle">
    <li class="subheader">
        <p>
            <span class="info">
                <a href="{$c->route('contact', $me->jid)}">
                    {$c->__('privacy.my_profile')}
                </a>
            </span>
            {$c->__('privacy.privacy_title')}
        </p>
    </li>
    <li>
        <span class="control">
            <form>
                <div class="control action">
                    <div class="checkbox">
                        <input
                            type="checkbox"
                            id="privacy"
                            name="privacy"
                            {if="$me->privacy"}
                                checked
                            {/if}
                            onchange="{$privacy}">
                        <label for="privacy"></label>
                    </div>
                </div>
            </form>
        </span>

        <p>{$c->__('privacy.privacy_question')}</p>
        <p class="all">{$c->__('privacy.privacy_info')}</p>
    </li>
</ul>

{if="$c->getUser()->isSupported('pubsub')"}
<div class="clear padded"></div>

<form name="vcard4" id="vcard4form" class="flex">
    <h3 class="block large">{$c->__('page.profile')}</h3>
    <div class="block">
        <input type="text" name="fn" class="content" value="{$me->fn}" placeholder="{$c->__('general.name')}">
        <label for="fn">{$c->__('general.name')}</label>
    </div>
    <div class="block">
        <input type="text" name="name" class="content" value="{$me->name}" placeholder="{$c->__('general.nickname')}">
        <label for="name">{$c->__('general.nickname')}</label>
    </div>

    <div class="block">
        <input type="email" name="email" class="content" value="{$me->email}" placeholder="{$c->__('general.email')}">
        <label for="fn">{$c->__('general.email')}</label>
    </div>

    <div class="block">
        <input type="text" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-[0-9]{4}" name="date" class="content" value="{$me->getDate()}" placeholder="DD-MM-YYYY">
        <label for="date">{$c->__('general.date_of_birth')}</label>
    </div>

    <div class="block large">
        <input type="url" name ="url" class="content" value="{$me->url}" placeholder="https://mywebsite.com/">
        <label for="url">{$c->__('general.website')}</label>
    </div>

    <div class="block large">
        <textarea name="desc" id="desctext" class="content" placeholder="{$c->__('general.about')}" onkeyup="MovimUtils.textareaAutoheight(this);">{$desc}</textarea>
        <label for="desc">{$c->__('general.about')}</label>
    </div>

    <div class="clear padded"></div>

    <div class="block">
        <input type="text" type="locality" name ="locality" class="content" value="{$me->adrlocality}" placeholder="{$c->__('position.locality')}">
        <label for="url">{$c->__('position.locality')}</label>
    </div>

    <div class="block">
        <div class="select">
            <select name="country">
                <option value=""></option>
                {loop="$countries"}
                    <option
                    {if="$value == $me->adrcountry"}
                        selected
                    {/if}
                    value="{$value}">{$value}</option>
                {/loop}
            </select>
        </div>
        <label for="country">{$c->__('position.country')}</label>
    </div>

    <div class="block large">
        <button
            onclick="
                {$submit}
                this.value = '{$c->__('button.submitting')}';
                this.className='button oppose inactive';"
            class="button color oppose"
            type="button"
            id="vcard4validate"
            >
            {$c->__('button.save')}
        </button>
        <button
            onclick="document.querySelector('#vcard4form').reset();"
            type="button"
            class="button flat oppose">
            {$c->__('button.reset')}
        </button>
    </div>
</form>
{else}
<ul class="list thick">
    <li>
        <span class="primary icon orange bubble color">
            <i class="zmdi zmdi-alert-triangle"></i>
        </span>
        <p>{$c->__('degraded.title')}</p>
        <p class="all">{$c->__('degraded.text_1')}</br>
        {$c->__('degraded.text_2')}</p>
        <p class="all">{$c->__('degraded.text_3')}</p>
    </li>
</ul>
{/if}
