<div class="quote" cite="{$embed->url|htmlspecialchars}">
    <ul>
        <li>
            <span>
                <a href="{$embed->url|htmlspecialchars}" target="_blank">{$embed->title|htmlentities}</a>
            </span>
            <p>{$embed->description|htmlentities}</p>
            <p>
                <a href="{$embed->providerUrl|htmlspecialchars}" target="_blank">{$embed->providerName|htmlentities}</a>
            </p>
        </li>
        {if="$embed->images != null"}
            <li>
                <a href="{$embed->images[0]['value']|htmlspecialchars}" target="_blank">
                    <img src="{$embed->images[0]['value']|htmlspecialchars}"/>
                </a>
            </li>
        {/if}
    </ul>
</div>
