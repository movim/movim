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
	{$link}
	<logo>{$logo}</logo>
	
	<generator uri="http://movim.eu/" version="{#APP_VERSION#}">
	  Movim
	</generator>
	
	<id>urn:uuid:{$uuid}</id>
	{loop="$messages"}
	<entry>
		<title>{$c->prepareTitle($value->title)}</title>
		<id>urn:uuid:{$c->generateUUID($value->content)}</id>
		<updated>{$c->prepareUpdated($value->published)}</updated>
		<content type="html">
			<![CDATA[{$c->prepareContent($value->content)}]]>
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
