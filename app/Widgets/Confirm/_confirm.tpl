<section id="confirm">
    <h3>{$c->__('confirm.title')}</h3>
    <br />
    <ul class="list thick">
        <li>
            <div>
                <p></p>
                <p class="center">{$c->__('confirm.description')}</p>
            </div>
            <h3>{$url}</h3>
        </li>
        <li>
            <div>
                <p></p>
                <p class="center">
                    {$c->__('confirm.code')}
                </p>
            </div>
            <h3>{$id}</h3>
        </li>
    </ul>
</section>
<div>
    <button
        onclick="Confirm_ajaxRefuse('{$from}', '{$id}', '{$url}', '{$method}'); Dialog_ajaxClear()"
        class="button flat">
        {$c->__('button.refuse')}
    </button>
    <button
        onclick="Confirm_ajaxAccept('{$from}', '{$id}', '{$url}', '{$method}'); Dialog_ajaxClear()"
        class="button flat">
        {$c->__('button.accept')}
    </button>
</div>
