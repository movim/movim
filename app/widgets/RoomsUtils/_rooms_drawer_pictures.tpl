<ul class="grid active">
    {loop="$pictures"}
        <li style="background-image: url('{$value->file->url|protectPicture}')"
            onclick="Preview_ajaxHttpShow('{$value->file->url}')">
            <i class="material-symbols">visibility</i>
        </li>
    {/loop}
</ul>

{if="$more"}
    <ul class="list middle" onclick="RoomsUtils.morePictures(this, '{$room}', {$page + 1})">
        <hr />
        <li class="active">
            <span class="primary icon gray">
                <i class="material-symbols">expand_more</i>
            </span>
            <div>
                <p class="line normal center">
                    {$c->__('button.more')}
                </p>
            </div>
        </li>
    </ul>
{/if}
