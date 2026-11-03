<section class="scroll">
    <form name="avatarform">
        <img class="avatar" src="{$me->getPicture(\Movim\ImageSize::O) ?? ''}">
        <div class="placeholder">
            <i class="material-symbols">image</i>
            <h1>{$c->__('avatar.missing')}</h1>
        </div>
        <input type="hidden" name="photobin"/>
    </form>
    <ul class="list thick divided">
        <li>
            <span class="primary icon bubble color green">
                <i class="material-symbols">attach_file</i>
            </span>
            <div>
                <p>{$c->__('avatar.file')}</p>
                <p><input type="file" onchange="MovimAvatar.file(this.files, 'avatarform');"></p>
            </div>
        </li>
        <li>
          <span class="primary icon bubble color blue">
            <i class="material-symbols">public</i>
          </span>
          <span class="control">
            <div class="checkbox">
              <input
                {if="$me->avatartype != \Moxl\Stanza\Avatar::NODE_METADATA"}checked{/if}
                type="checkbox"
                id="avatar_public"
                name="public"
                form="avatarform"/>
              <label for="avatar_public"></label>
            </div>
          </span>
          <div>
            <p>{$c->__('avatar.public')}</p>
            <p class="all">{$c->__('avatar.public_text')}</p>
          </div>
        </li>
    </ul>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button
        type="button"
        onclick="
            Avatar_ajaxSubmit(MovimUtils.formToJson('avatarform'));
            this.value = '{$c->__('button.submitting')}';
            this.className='button flat inactive';"
        class="button flat"
        >
        {$c->__('button.submit')}
    </button>
</footer>
