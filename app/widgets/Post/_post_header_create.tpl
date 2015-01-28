<div>
    <span class="on_desktop icon"><i class="md md-view-list"></i></span>
    <h2>{$c->__('page.news')}</h2>
</div>
<div>
    <h2 class="active r3" onclick="MovimTpl.hidePanel(); Post_ajaxClear();">
        <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
        New Post
    </h2>
    <ul class="active">
        <li onclick="Post_ajaxHelp()">
            <span class="icon">
                <i class="md md-help"></i>
            </span>
        </li>
        <li onclick="Post_ajaxPreview(movim_form_to_json('post'))">
            <span class="icon">
                <i class="md md-remove-red-eye"></i>
            </span>
        </li>
        <li onclick="Post_ajaxPublish(movim_form_to_json('post'))">
            <span class="icon">
                <i class="md md-send"></i>
            </span>
        </li>
    </ul>
</div>
