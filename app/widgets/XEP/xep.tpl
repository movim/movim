{if="isset($key) && isset($xml)"}
<div id="xep_widget" class="tabelem paddedtop" title="XEP">
    <article class="block">        
        <a
            target="_blank"
            class="button oppose color green merged right"
            href="http://xmpp.org/extensions/xep-{$key}.html">
            <i class="fa fa-eye"></i> {$c->__('xep.original')}
        </a>
        
        <a
            class="button oppose color black merged left"
            href="{$c->route('about', null, 'caps_widget')}">
            <i class="fa fa-angle-left"></i> {$c->__('button.return')}
        </a>
        
        <header>
            <span class="title">XEP-{$xml->header->number}: {$xml->header->title}</span>
            <span><i class="fa fa-thumb-tack"></i> {$c->__('xep.status')} : {$xml->header->status}</span>
            - <span><i class="fa fa-tag"></i> {$c->__('xep.type')} : {$xml->header->type}</span>

            <div class="content">
                <h1><i class="fa fa-user"></i> {$c->__('xep.authors')}</h1>
                <dl>
                    {loop="$xml->header->author"}
                        <di>
                            <dt>{$value->firstname} {$value->surname}</dt>
                            {$a = $c->getJid($value->jid)}
                            <dd>
                                <img class="avatar" src="{$a->getPhoto('m')}"/>
                                
                                <i class="fa fa-envelope"></i> <span class="email">{$value->email|strrev}</span>
                                <br/><i class="fa fa-comment"></i> <span class="email">{$value->jid|strrev}</span>

                                {if="isset($value->uri)"}
                                <br/><i class="fa fa-globe"></i> {$value->uri}
                                {/if}
                            </dd>
                        </di>
                    {/loop}
                </dl>

                <h1><i class="fa fa-refresh"></i> {$c->__('xep.revisions')}</h1>
                <dl>
                    {loop="$xml->header->revision"}
                        <di>
                            <dt>{$value->version}</dt>
                            {$a = $c->getJid($value->jid)}
                            <dd>
                                <span class="date"><i class="fa fa-clock-o"></i> {$value->date}</span>
                                
                                <br />{$value->remark->asXML()}
                            </dd>
                        </di>
                    {/loop}
                </dl>
            </div>
        </header>
    </article>
    
    <article class="block">
        <h1>{$c->__('xep.abstract')}</h1>
        <p class="summary">
            {$xml->header->abstract}
        </p>

        <div class="content">
        {loop="$xml->section1"}
            <h1>{$value->attributes()->topic}</h1>
            {loop="$value->children()"}
                {if="$value->getName() == 'section2'"}
                    <h2>{$value->attributes()->topic}</h2>
                    {loop="$value->children()"}
                        {if="$value->getName() == 'section3'"}
                            <h3>{$value->attributes()->topic}</h3>
                            {loop="$value->children()"}
                                {if="$value->getName() == 'section4'"}
                                    <h4>{$value->attributes()->topic}</h4>
                                    {loop="$value->children()"}
                                        {if="$value->getName() == 'example'"}
                                            <figure>
                                                <pre>{$value|htmlentities}</pre>
                                                <figcaption>{$value->attributes()->caption}</figcaption>
                                            </figure>
                                        {else}
                                            {$value->asXML()}
                                        {/if}
                                    {/loop}
                                {elseif="$value->getName() == 'example'"}
                                    <figure>
                                        <pre>{$value|htmlentities}</pre>
                                        <figcaption>{$value->attributes()->caption}</figcaption>
                                    </figure>                   
                                {elseif="$value->getName() == 'code'"}
                                    <figure>
                                        <pre>{$value|htmlentities}</pre>
                                    </figure>                   
                                {else}
                                    {$value->asXML()}
                                {/if}
                            {/loop}
                        {elseif="$value->getName() == 'example'"}
                            <figure>
                                <pre>{$value|htmlentities}</pre>
                                <figcaption>{$value->attributes()->caption}</figcaption>
                            </figure>                   
                        {elseif="$value->getName() == 'code'"}
                            <figure>
                                <pre>{$value|htmlentities}</pre>
                            </figure>                   
                        {else}
                            {$value->asXML()}
                        {/if}
                    {/loop}
                {elseif="$value->getName() == 'example'"}
                    <figure>
                        <pre>{$value|htmlentities}</pre>
                        <figcaption>{$value->attributes()->caption}</figcaption>
                    </figure>                   
                {elseif="$value->getName() == 'code'"}
                    <figure>
                        <pre>{$value|htmlentities}</pre>
                    </figure>                   
                {else}
                    {$value->asXML()}
                {/if}
            {/loop}
        {/loop}
        </div>

    </article>
</div>
{/if}
