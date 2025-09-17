<header>
    <ul class="list thick">
        <li>
            <span
                class="primary icon active"
                {if="$draft->node == 'urn:xmpp:microblog:0'"}
                    onclick="MovimUtils.reload('{$c->route('news')}');"
                {else}
                    onclick="history.back();"
                {/if}
            >
                <i class="material-symbols">arrow_back</i>
            </span>

            {if="$icon != null"}
                <span class="primary icon bubble">
                    <img src="{$icon->getPicture(\Movim\ImageSize::L)}"/>
                </span>
            {else}
                <span class="primary icon bubble color {$draft->node|stringToColor}">
                    {$draft->node|firstLetterCapitalize}
                </span>
            {/if}

            <div>
                <p class="line">
                    {if="$draft->isNotEmpty()"}
                        {$c->__('button.edit')}
                    {else}
                        {$c->__('publish.new')}
                    {/if}
                </p>
                <p>{$c->__('publish.rich_editor')}</p>
            </div>
        </li>
    </ul>
</header>

<div class="block">
    <ul class="tabs wide two">
        <li {if="$type == 'brief'"}class="active"{/if}>
            <a href="#" onclick="Publish.get('brief')" title="{$c->__('publish.brief_title')}">
                <i class="material-symbols">short_text</i> &nbsp; {$c->__('publish.brief_title')}
            </a>
        </li>
        <li {if="$type == 'article'"}class="active"{/if}>
            <a href="#" onclick="Publish.get('article')" title="{$c->__('publish.post_title')}">
                <i class="material-symbols">newspaper</i> &nbsp; {$c->__('publish.post_title')}
            </a>
        </li>
    </ul>
    <ul class="list">
        {if="isset($replyblock)"}
            <br />
            <li>
                <span class="control icon gray active"
                      onclick="Publish.clearReply()">
                    <i class="material-symbols">close</i>
                </span>
                <div>
                    <ul class="list card middle" id="publishreply">
                        {autoescape="off"}
                            {$replyblock}
                        {/autoescape}
                    </ul>
                </div>
            </li>
        {/if}
        <li>
            <form onsubmit="return false;" name="brief" data-type="{$type}">
                <input type="hidden" name="id" value="{$draft->id}">
                <div id="title">
                    <textarea
                        dir="auto"
                        name="title"
                        id="title"
                        rows="1"
                        required
                        data-autoheight="true"
                        placeholder="{$c->__('publish.placeholder')}"
                        type="text">{$draft->title ?? ''}</textarea>
                    <label for="desc">{if="$type == 'article'"}{$c->__('publish.title')}{/if} <span class="save"><i class="material-symbols">check</i></span></label>
                </div>
                <div id="content">
                    <textarea
                        dir="auto"
                        name="content"
                        placeholder="{$c->__('publish.content_text')}"
                        data-autoheight="true"
                        >{$draft->content ?? ''}</textarea>
                    <label for="desc">{$c->__('publish.content')} <span class="save"><i class="material-symbols">check</i></span></label>
                    <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('publish.help_hashtag')}</span>
                </div>
            </form>
        </li>
    </ul>
    <ul class="list flex card middle" id="publishembeds">
        {loop="$draft->embeds"}
            {autoescape="off"}
                {$c->prepareEmbed($value)}
            {/autoescape}
        {/loop}
    </ul>

    <ul class="list">
        {autoescape="off"}
            {$c->prepareToggles($draft)}
        {/autoescape}
    </ul>

    <ul class="list">
        <li>
            <div>
                <button
                    class="button narrow flat icon gray"
                    title="{$c->__('publish.add_link')}"
                    onclick="Publish_ajaxLink()">
                    <i class="material-symbols">add_link</i>
                </button>
                {if="$c->me->hasUpload()"}
                    <button
                        class="button narrow flat icon gray"
                        title="{$c->__('publish.attach')}"
                        onclick="Upload_ajaxGetPanel()">
                        <i class="material-symbols">add_photo_alternate</i>
                    </button>
                    <button
                        class="button narrow flat icon gray"
                        title="{$c->__('publish.add_snap')}"
                        onclick="Snap.init()">
                        <i class="material-symbols">camera_alt</i>
                    </button>
                    <button
                        class="button narrow flat icon gray"
                        title="{$c->__('draw.title')}"
                        onclick="Draw_ajaxHttpGet()">
                        <i class="material-symbols">gesture</i>
                    </button>
                {/if}

                <button class="button send oppose color" onclick="Publish.publish()">
                    <i class="material-symbols">send</i>
                    <span class="on_desktop">{$c->__('page.publish')}</span>
                </button>
                <button class="button flat oppose gray" onclick="Publish.preview()">
                    <i class="material-symbols">visibility</i>
                    <span class="on_desktop">{$c->__('publish.preview')}</span>
                </button>
                <button class="button flat oppose gray on_mobile" onclick="PublishHelp_ajaxDrawer()">
                    <i class="material-symbols">help</i>
                </button>
            </div>
        </li>
    </ul>
</div>