<ul class="list middle divided spaced">
{loop="$post->links"}
    {if="!substr($value->href, 0, strlen(BASE_URI) == BASE_URI)"}
        <li>
            <span class="primary icon gray">
                {if="$value->logo"}
                    <img src="{$value->logo|protectPicture}"/>
                {else}
                    <i class="material-icons">link</i>
                {/if}
            </span>
            <div>
                <p class="normal line">
                    <a target="_blank" href="{$value->href}" title="{$value->href}">
                        {if="!empty($value->title)"}
                            {$value->title}
                        {else}
                            {$value->href}
                        {/if}
                    </a>
                </p>
                {if="$value->description"}
                    <p title="{$value->description}">{$value->description}</p>
                {else}
                    <p>{$value->url.host}</p>
                {/if}
            </div>
        </li>
    {/if}
{/loop}

{loop="$post->files"}
    <li>
        <span class="primary icon gray">
            <span class="material-icons">attach_file</span>
        </span>
        <div>
            <p class="normal line">
                <a
                    href="{$value->href}"
                    class="enclosure"
                    {if="isset($value->type)"}
                        type="{$value->type}"
                    {/if}
                    target="_blank">
                    {$value->href|urldecode}
                </a>
            </p>
        </div>
    </li>
{/loop}
</ul>
