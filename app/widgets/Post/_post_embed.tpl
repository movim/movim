<q cite="{$embed->url}">
    <ul>
        <li>
            <span>
                <a href="{$embed->url|htmlspecialchars}" target="_blank">{$embed->title}</a>
            </span>
            <p>{$embed->description}</p>
            <p>
                <a href="{$embed->providerUrl|htmlspecialchars}" target="_blank">{$embed->providerName}</a>
            </p>
        </li>
        {if="key($embed->images) != null"}
            <li>
                <a href="{$embed->images|key|htmlspecialchars}" target="_blank">
                    <img src="{$embed->images|key|htmlspecialchars}"/>
                </a>
            </li>
        {/if}
    </ul>
</q>
