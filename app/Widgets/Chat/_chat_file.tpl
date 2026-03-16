<li class="block">
    <span class="primary icon bubble gray">
        <i class="material-symbols fill">{$file->type|mimeToIcon}</i>
    </span>
    <div>
        <p class="line"><a href="{$file->url}" target="_blank" rel="noopener noreferrer">{$file->name}</a></p>
        {if="$file->cleansize"}
            <p>{$file->cleansize} · {$file->type|mimeToLabel}</p>
        {/if}
    </div>
</li>