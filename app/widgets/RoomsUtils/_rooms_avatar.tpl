<section class="scroll">
    <form name="avatarroom">
        <img class="avatar" src="{$room->getPhoto('o') ?? ''}">
        <div class="placeholder">
            <i class="material-icons">image</i>
            <h1>{$c->__('avatar.missing')}</h1>
        </div>
        <input type="hidden" name="photobin"/>
    </form>
    <ul class="list thick divided">
        <li>
            <span class="primary icon bubble color green">
                <i class="material-icons">attach_file</i>
            </span>
            <div>
                <p>{$c->__('avatar.file')}</p>
                <p><input type="file" onchange="MovimAvatar.file(this.files, 'avatarroom');"></p>
            </div>
        </li>
    </ul>
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button
        type="button"
        onclick="
            RoomsUtils_ajaxSetAvatar('{$room->conference}', MovimUtils.formToJson('avatarroom'));
            this.value = '{$c->__('button.submitting')}';
            this.className='button flat inactive';"
        class="button flat"
        >
        {$c->__('button.submit')}
    </button>
</div>
