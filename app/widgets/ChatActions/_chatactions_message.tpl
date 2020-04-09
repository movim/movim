<section>
    <ul class="list divided active">
        <li onclick="ChatActions_ajaxEditMessage({$message->mid})">
            <span class="control icon gray">
                <i class="material-icons">edit</i>
            </span>
            <content>
                <p class="normal">Edit</p>
            </content>
        </li>
        <li onclick="ChatActions_ajaxHttpDaemonRetract({$message->mid})">
            <span class="control icon gray">
                <i class="material-icons">delete</i>
            </span>
            <content>
                <p class="normal">Retract</p>
            </content>
        </li>
    </ul>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>