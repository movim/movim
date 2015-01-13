<span id="back" class="icon" onclick="MovimTpl.hidePanel(); Chat_ajaxGet();"><i class="md md-arrow-back"></i></span>

<ul class="active">
    <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
        <span class="icon">
            <i class="md md-delete"></i>
        </span>
    </li>
    <li onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel();">
        <span class="icon">
            <i class="md md-close"></i>
        </span>
    </li>
</ul>
<h2>{$room}</h2>
