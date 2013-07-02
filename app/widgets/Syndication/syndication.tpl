{if="isset($from)"}  
    {if="!empty($messages)"}        
        <?xml version="1.0" encoding="utf-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title>{$title}</title>
            <updated>{$date}</updated>
            <author>
                <name>{$name}</name>
                <uri>{$uri}</uri>
            </author>
            <link rel="self" href="{$link}" />
            <logo>{$logo}</logo>
            
            <generator uri="http://movim.eu/" version="{#APP_VERSION#}">
              Movim
            </generator>
            
            <id>urn:uuid:60a76c80-d399-11d9-b91C-0003939e0af6</id>
            {loop="messages"}
            <entry>
                <title>{$c->prepareTitle($value->title)}</title>
                <id>urn:uuid:{$value->nodeid}</id>
                <updated>{$c->prepareUpdated($value->published)}</updated>
                <content type="html">
                    <![CDATA[{$c->prepareContent($value->content)}]]>
                </content>
            </entry>
            {/loop}
        </feed>
    {else}
        {$c->t('No public feed for this contact')}
    {/if}
{else}
    {$c->t('No contact specified')}
{/if}
