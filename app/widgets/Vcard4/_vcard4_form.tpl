{if="$me->hasPubsub()"}
<form name="vcard4" id="vcard4form" class="flex">
    <div class="block">
        <input dir="auto" type="text" name="fn" value="{$contact->fn ?? ''}" placeholder="{$c->__('general.name')}">
        <label for="fn">{$c->__('general.name')}</label>
    </div>
    <div class="block">
        <input dir="auto" type="text" name="name" value="{$contact->name ?? ''}" placeholder="{$c->__('general.nickname')}">
        <label for="name">{$c->__('general.nickname')}</label>
    </div>

    <div class="block">
        <input type="email" name="email" value="{$contact->email ?? ''}" placeholder="{$c->__('general.email')}">
        <label for="fn">{$c->__('general.email')}</label>
    </div>

    <div class="block">
        <input type="date" name="date" value="{$contact->getDate() ?? ''}" placeholder="YYYY-M-MDD-MM">
        <label for="date">{$c->__('general.date_of_birth')}</label>
    </div>

    <div class="block large">
        <input type="url" name ="url" value="{$contact->url ?? ''}" placeholder="https://mywebsite.com/">
        <label for="url">{$c->__('general.website')}</label>
    </div>

    <div class="block large">
        <textarea dir="auto" name="desc" id="desctext" placeholder="{$c->__('general.about')}" data-autoheight="true">{$desc ?? ''}</textarea>
        <label for="desc">{$c->__('general.about')}</label>
    </div>

    <div class="clear padded"></div>

    <div class="block">
        <input dir="auto" type="text" name ="locality" class="content" value="{$contact->adrlocality ?? ''}" placeholder="{$c->__('position.locality')}">
        <label for="url">{$c->__('position.locality')}</label>
    </div>

    <div class="block">
        <div class="select">
            <select name="country">
                <option value="">{$c->__('position.country')}</option>
                {loop="$countries"}
                    <option
                    {if="$value == $contact->adrcountry"}
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
                Vcard4_ajaxVcardSubmit(MovimUtils.formToJson('vcard4'));
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
            <i class="material-icons">warning</i>
        </span>
        <div>
            <p>{$c->__('degraded.title')}</p>
            <p class="all">{$c->__('degraded.text_1')}</br>
            {$c->__('degraded.text_2')}</p>
        </div>
    </li>
</ul>
{/if}

<hr />
<br />

<ul class="list middle active">
    <li onclick="Vcard4_ajaxEditNickname()">
        <span class="control icon gray">
            <i class="material-icons">edit</i>
        </span>
        <div>
            <p>{$c->__('profile.info')}</p>
            <p class="all">{$c->__('profile.nickname_info')}</p>
            {if="!empty($me->nickname)"}
                <p>{$c->__('profile.nickname_set', $me->nickname)}</p>
            {/if}
        </div>
    </li>
    <li>
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
                            onchange="Vcard4_ajaxChangePrivacy(this.checked)">
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
</ul>
