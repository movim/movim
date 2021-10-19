<section>
    <ul class="list divided active">
        <li onclick="Stickers_ajaxReaction({$message->mid})">
            <span class="control icon gray">
                <i class="material-icons">mood</i>
            </span>
            <div>
                <p class="normal">{$c->__('message.react')}</p>
            </div>
        </li>
        <!--<li onclick="ChatActions_ajaxEditMessage({$message->mid})">
            <span class="control icon gray">
                <i class="material-icons">edit</i>
            </span>
            <div>
                <p class="normal">{$c->__('button.edit')}</p>
            </div>
        </li>-->
        <li onclick="ChatActions_ajaxHttpDaemonRetract({$message->mid})">
            <span class="control icon gray">
                <i class="material-icons">delete</i>
            </span>
            <div>
                <p class="normal">{$c->__('message.retract')}</p>
            </div>
        </li>
    </ul>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>