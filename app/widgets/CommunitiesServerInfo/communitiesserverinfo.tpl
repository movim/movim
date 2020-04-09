{if="$info && (!empty($info->adminaddresses) || !empty($info->abuseaddresses) || !empty($info->supportaddresses))"}
    {$addresses = array_unique(array_merge($info->adminaddresses, $info->abuseaddresses, $info->supportaddresses))}
    <ul class="list">
        <li class="subheader">
            <div>
                <p>{$c->__('contact.title')}</p>
            </div>
        </li>
        {loop="$addresses"}
            <li>
                {$parsed = parse_url($value)}
                {if="$parsed['scheme'] == 'xmpp'"}
                    {if="isset($parsed['query']) && $parsed['query'] == 'join'"}
                    <span class="primary icon gray">
                        <i class="material-icons">chat</i>
                    </span>
                    <div>
                        <p class="normal">
                            <a href="{$c->route('chat', [$parsed['path'], 'room'])}">
                                {$parsed['path']}
                            </a>
                        </p>
                        {else}
                        <span class="primary icon gray">
                            <i class="material-icons">comment</i>
                        </span>
                        <p class="normal">
                            <a href="{$c->route('chat', $parsed['path'])}">
                                {$parsed['path']}
                            </a>
                        </p>
                    </div>
                    {/if}
                {else}
                    <span class="primary icon gray">
                        <i class="material-icons">email</i>
                    </span>
                    <div>
                        <p class="normal">
                            <a href="{$value}" target="_blank" rel="noopener noreferrer">
                                {$parsed['path']}
                            </a>
                        </p>
                    </div>
                {/if}
            </li>
        {/loop}
    </ul>
{/if}
