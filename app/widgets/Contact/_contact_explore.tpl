<header>
    <ul class="list middle">
        <li>
            <span class="primary icon active gray on_mobile" onclick="MovimTpl.hidePanel()">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            <span class="control"></span>
            <p class="line">
                {$c->__('explore.explore')}
            </p>
        </li>
    </ul>
</header>

<div id="public_list">
    {$users}
</div>
