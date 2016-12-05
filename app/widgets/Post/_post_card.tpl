<article class="block large">
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
            {if="$post->isMicroblog()"}
                <i class="zmdi zmdi-account"></i>
            {else}
                <i class="zmdi zmdi-group-work"></i>
            {/if}
            </span>
            <p class="normal">{$post->title}</p>
            <p>
                {if="$post->isMicroblog()"}
                    <a href="{$c->route('contact', $post->getContact()->jid)}">
                        {$post->getContact()->getTrueName()}
                    </a> –
                {else}
                    <a href="{$c->route('group', $post->origin)}">
                        {$post->origin}
                    </a> /
                    <a href="{$c->route('group', [$post->origin, $post->node])}">
                        {$post->node}
                    </a> –
                {/if}
                {$post->published|strtotime|prepareDate}
                {if="$post->published != $post->updated"}
                     – <i class="zmdi zmdi-edit"></i> {$post->updated|strtotime|prepareDate:true,true}
                {/if}
            </p>
        </li>
    </ul>
    <ul class="list">
        <li class="active">
            <p>
            </p>
            <p>
                <section {if="!$post->isShort()"}class="limited"{/if}>
                    <content>
                        {if="$post->isShort() && isset($attachments.pictures)"}
                            {loop="$attachments.pictures"}
                                {if="$value.type != 'picture'"}
                                <a href="{$value.href}" class="alternate" target="_blank">
                                    <img class="big_picture" type="{$value.type}" src="{$value.href|urldecode}"/>
                                </a>
                                {/if}
                            {/loop}
                        {/if}
                        {if="$post->getYoutube()"}
                            <div class="video_embed">
                                <iframe src="https://www.youtube.com/embed/{$post->getYoutube()}" frameborder="0" allowfullscreen></iframe>
                            </div>
                        {/if}
                        {$post->contentcleaned}
                    </content>
                <section>
            </p>
        </li>

        <li>
            <p class="normal">
                {$tags = $post->getTags()}
                {if="isset($tags)"}
                    {loop="$tags"}
                        <a target="_blank" href="{$c->route('tag', [$value])}">#{$value}</a>
                    {/loop}
                {/if}
            </p>
            <p class="normal">
                <a class="button flat gray">
                    {$post->countComments()} <i class="zmdi zmdi-comment"></i>
                </a>
                <a class="button flat gray">
                    <i class="zmdi zmdi-share"></i>
                </a>
                <a class="button flat oppose" href="{$c->route('post', [$post->origin, $post->node, $post->nodeid])}">
                    Read more
                </a>
            </p>
        </li>
    </ul>
</article>
