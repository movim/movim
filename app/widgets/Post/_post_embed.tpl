<div class="card">
    <div>
        <ul class="thick simple">
            <li class="condensed">
                {if="isset($embed->images[0])"}
                    <img src="{$embed->images[0]}">
                {/if}
                <span>{$embed->title}</span>
                <p>{$embed->description}</p>
            </li>
        </ul>
    </div>
</div>
