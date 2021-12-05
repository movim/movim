<ul class="grid active">
    {loop="$pictures"}
        <li style="background-image: url('{$value->file['uri']|protectPicture}')"
            onclick="Preview_ajaxHttpShow('{$value->file['uri']}')">
            <i class="material-icons">visibility</i>
        </li>
    {/loop}
</ul>

{if="$more"}
    <ul class="list middle" onclick="ContactActions.morePictures(this, '{$jid}', {$page + 1})">
        <hr />
        <li class="active">
            <span class="primary icon gray">
                <i class="material-icons">expand_more</i>
            </span>
            <div>
                <p class="line normal center">
                    {$c->__('button.more')}
                </p>
            </div>
        </li>
    </ul>
{/if}