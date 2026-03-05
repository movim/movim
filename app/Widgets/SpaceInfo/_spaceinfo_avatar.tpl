<section class="scroll">
    <form name="spaceavatar">
        <img class="avatar" src="{$info->getPicture(\Movim\ImageSize::O, placeholder: $info->name) ?? ''}">
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
                <p><input type="file" onchange="MovimAvatar.file(this.files, 'spaceavatar');"></p>
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
            SpaceInfo_ajaxSetAvatar('{$info->server}', '{$info->node}', MovimUtils.formToJson('spaceavatar'));
            this.value = '{$c->__('button.submitting')}';
            this.className='button flat inactive';"
        class="button flat"
        >
        {$c->__('button.submit')}
    </button>
</footer>
