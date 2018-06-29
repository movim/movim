<ul class="list thick">
    <li>
        {if="!$public"}
            <span class="primary icon gray active" onclick="history.back();">
                <i class="material-icons">arrow_back</i>
            </span>
        {/if}

        {if="($public && $post->open) || !$public"}
            {if="$repost"}
                {$contact = $repost}
            {else}
                {$contact = $post->contact}
            {/if}

            {if="$post->isMicroblog()"}
                {if="$post->nsfw"}
                    <span class="primary icon bubble color red tiny">
                        +18
                    </span>
                {else}
                    {if="$contact"}
                        {$url = $contact->getPhoto('s')}

                        {if="$url"}
                            <span class="icon primary bubble">
                                <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                                    <img src="{$url}">
                                </a>
                            </span>
                        {else}
                            <span class="icon primary bubble color {$contact->jid|stringToColor}">
                                <a href="#" onclick="Post_ajaxGetContact('{$contact->jid}')">
                                    <i class="material-icons">person</i>
                                </a>
                            </span>
                        {/if}
                    {else}
                        <span class="icon primary bubble color {$post->aid|stringToColor}">
                            <a href="#" onclick="Post_ajaxGetContact('{$post->aid}')">
                                <i class="material-icons">person</i>
                            </a>
                        </span>
                    {/if}
                {/if}
            {else}
                {if="$post->nsfw"}
                    <span class="primary icon bubble color red tiny">
                        +18
                    </span>
                {elseif="$post->logo"}
                    <span class="primary icon bubble">
                        <a href="{$c->route('community', [$post->server, $post->node])}">
                            <img src="{$post->getLogo()}">
                        </a>
                    </span>
                {else}
                    <span class="primary icon bubble color {$post->node|stringToColor}">
                        <a href="{$c->route('community', [$post->server, $post->node])}">
                            {$post->node|firstLetterCapitalize}
                        </a>
                    </span>
                {/if}
            {/if}

            {if="$public"}
            <span class="control icon active">
                <a  {if="$public"}
                    {if="$post->isMicroblog()"}
                    href="{$c->route('blog', [$post->server, $post->nodeid])}"
                    {else}
                    href="{$c->route('node', [$post->server, $post->node, $post->nodeid])}"
                    {/if}
                {else}
                    href="{$c->route('post', [$post->server, $post->node, $post->nodeid])}"
                {/if}
                >
                    <i class="material-icons">chevron_right</i>
                </a>
            </span>
        {/if}

        {if="!$public && $post->isMine()"}
            {if="$post->isEditable()"}
                <span class="control icon active gray"
                      onclick="MovimUtils.redirect('{$c->route('publish', [$post->server, $post->node, $post->nodeid])}')"
                      title="{$c->__('button.edit')}">
                    <i class="material-icons">edit</i>
                </span>
            {/if}
            <span class="control icon active gray"
                  onclick="PostActions_ajaxDelete('{$post->server}', '{$post->node}', '{$post->nodeid}')"
                  title="{$c->__('button.delete')}">
                <i class="material-icons">delete</i>
            </span>
        {/if}

            {if="!$post->isBrief()"}
                <p {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                    {$post->getTitle()|addHashtagsLinks}
                </p>
            {else}
                <p></p>
            {/if}
            <p>
                {if="$contact"}
                    {if="!$public"}
                    <a href="#" onclick="if (typeof Post_ajaxGetContact == 'function') { Post_ajaxGetContact('{$contact->jid}'); } else { Group_ajaxGetContact('{$contact->jid}'); } ">
                    {/if}
                    {$contact->truename}
                    {if="!$public"}</a>{/if} –
                {/if}
                {if="!$post->isMicroblog()"}
                    {if="!$public"}
                    <a href="{$c->route('community', $post->server)}">
                    {/if}
                        {$post->server}
                    {if="!$public"}</a>{/if} /
                    {if="!$public"}
                    <a href="{$c->route('community', [$post->server, $post->node])}">
                    {/if}
                        {$post->node}
                    {if="!$public"}</a>{/if} –
                {/if}
                {$post->published|strtotime|prepareDate}
                {if="$post->published != $post->updated"}
                    - <i class="material-icons">edit</i> {$post->updated|strtotime|prepareDate}
                {/if}
            </p>
            {if="$post->isBrief()"}
                <p class="normal">
                    {$post->getTitle()|addUrls|addHashtagsLinks|nl2br|prepareString}
                </p>
            {/if}
        {/if}
    </li>
</ul>