<li class="block" id="{$embed->HTMLId}">
    <span class="control active icon gray divided" onclick="Publish_ajaxHttpRemoveEmbed({$embed->draft_id}, {$embed->id})">
        <i class="material-symbols">close</i>
    </span>
    <div>
        <p><i class="icon gray material-symbols">link</i><span class="second">URL</span></p>
        <p class="line two">
            <a href="{$embed->url}" target="_blank">
                {$embed->url}
            </a>
        </p>
    </div>
</li>
