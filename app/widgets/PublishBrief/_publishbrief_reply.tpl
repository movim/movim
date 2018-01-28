<li class="block large">
    {if="$reply->picture"}
        <span
            class="primary icon thumb"
            style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture});">
            <i class="zmdi zmdi-share"></i>
        </span>
    {/if}
    <p class="line">{$reply->title}</p>
    <p>{$reply->getSummary()}</p>
    <p>
        {if="$reply->isMicroblog()"}
            <i class="zmdi zmdi-account"></i> {$reply->getContact()->getTrueName()}
        {else}
            <i class="zmdi zmdi-pages"></i> {$reply->node}
        {/if}
        <span class="info">
            {$reply->published|strtotime|prepareDate:true,true}
        </span>
    </p>
</li>
