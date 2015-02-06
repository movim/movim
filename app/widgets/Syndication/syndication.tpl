{if="isset($contact)"}  
    {if="!empty($messages)"}        
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{$c->__('feed.title', $contact->getTrueName())}</title>
    <updated>{$date}</updated>
    <author>
        <name>{$contact->getTrueName()}</name>
        <uri>{$uri}</uri>
    </author>
    {$link}
    <logo>{$contact->getPhoto('xl')}</logo>

    <generator uri="http://movim.eu/" version="{#APP_VERSION#}">
      Movim
    </generator>

    <id>urn:uuid:{$uuid}</id>
    {loop="$messages"}
    <entry>
        <title>
            {if="$value->title != null"}
                {$c->prepareTitle($value->title)}
            {else}
                {$c->__('post.default_title')}
            {/if}
        </title>
        <id>urn:uuid:{$c->generateUUID($value->content)}</id>
        <updated>{$c->prepareUpdated($value->published)}</updated>
        <content type="html">
            {if="$value->contentcleaned"}
                <![CDATA[{$value->contentcleaned}]]>
            {else}
                <![CDATA[{$value->content|html_entity_decode|prepareString}]]>
            {/if}
        </content>
    </entry>
    {/loop}
</feed>
    {else}
        {$c->__('feed.no')}
    {/if}
{else}
    {$c->__('feed.no_contact')}
{/if}
