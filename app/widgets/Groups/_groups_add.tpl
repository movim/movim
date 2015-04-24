<section>
    <form name="groupadd">
        <h3>{$c->__('groups.add')}</h3>

        <div>
            <input name="name" placeholder="{$c->__('groups.name_example')}" type="text" required />
            <label for="name">{$c->__('groups.name')}</label>
        </div>
    </section>
    <div>
        <a class="button flat" onclick="Dialog.clear()">
            {$c->__('button.close')}
        </a>
        <a
            class="button flat"
            onclick="Groups_ajaxAddConfirm('{$server}', movim_form_to_json('groupadd')); Dialog.clear();">
            {$c->__('button.add')}
        </a>
    </div>

</div>
