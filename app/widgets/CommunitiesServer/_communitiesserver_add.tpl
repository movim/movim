<section>
    <form name="groupadd" onsubmit="return false;">
        <h3>{$c->__('communitiesserver.add', $server)}</h3>

        <div>
            <input name="name" placeholder="{$c->__('communitiesserver.name_example')}" type="text" required />
            <label for="name">{$c->__('communitiesserver.name')}</label>
        </div>
    </section>
    <div>
        <a class="button flat" onclick="Dialog_ajaxClear()">
            {$c->__('button.close')}
        </a>
        <a
            class="button flat"
            onclick="CommunitiesServer_ajaxAddConfirm('{$server}', MovimUtils.formToJson('groupadd')); Dialog_ajaxClear();">
            {$c->__('button.add')}
        </a>
    </div>

</div>
