<section>
    <form name="spacesmenu_create">
        <h3>{$c->__('spacesmenu.create_space_title')}</h3>
        <div>
            <input name="title" placeholder="{$c->__('spacesmenu.space_title')}" required/>
            <label for="title">{$c->__('spacesmenu.space_title')}</label>
        </div>
    </form>
</section>
<footer>
    <button class="button flat" onclick="SpacesMenu_ajaxAdd()">
        {$c->__('button.return')}
    </button>
    <button
        class="button flat"
        onclick="SpacesMenu_ajaxCreateConfirm(MovimUtils.formToJson('spacesmenu_create'));"
        >
        {$c->__('button.create')}
    </button>
</footer>
