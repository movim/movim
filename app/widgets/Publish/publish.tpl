<div id="publish" class="card shadow">
    <header>
        <ul class="list thick">
            <li>
                <span class="control privacy"
                title="{$c->__('post.public')}">
                    <form>
                        <div class="control action">
                            <div class="checkbox">
                                <input
                                    id="public"
                                    name="public"
                                    onchange="Publish_ajaxTogglePrivacy({$draft->id}, this.checked)"
                                    {if="$draft && $draft->open"}
                                        checked
                                    {/if}
                                    type="checkbox">
                                <label for="public">
                                    <i class="material-icons"></i>
                                </label>
                            </div>
                        </div>
                    </form>
                </span>
                <span
                    class="primary icon active"
                    {if="$draft->node == 'urn:xmpp:microblog:0'"}
                        onclick="MovimUtils.redirect('{$c->route('news')}');"
                    {else}
                        onclick="history.back();"
                    {/if}
                >
                    <i class="material-icons">arrow_back</i>
                </span>

                {if="$icon != null"}
                    {$url = $icon->getPhoto('l')}
                    {if="$url"}
                        <span class="primary icon bubble">
                            <img src="{$url}"/>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$draft->node|stringToColor}">
                            {$draft->node|firstLetterCapitalize}
                        </span>
                    {/if}
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
        <br />
        <ul class="list">
            {if="isset($replyblock)"}
                <li>
                    <span class="control icon gray active"
                          onclick="Publish.clearReply()">
                        <i class="material-icons">close</i>
                    </span>
                    <div>
                        <ul class="list card shadow middle" id="publishreply">
                            {autoescape="off"}
                                {$replyblock}
                            {/autoescape}
                        </ul>
                    </div>
                </li>
                <br />
            {/if}
            <li>
                <form onsubmit="return false;" name="brief">
                    <input type="hidden" name="id" value="{$draft->id}">
                    <div>
                        <textarea
                            dir="auto"
                            name="title"
                            id="title"
                            rows="1"
                            required
                            data-autoheight="true"
                            placeholder="{$c->__('publish.placeholder')}"
                            type="text">{$draft->title ?? ''}</textarea>
                        <label for="desc">{$c->__('publish.title')} <span class="save"><i class="material-icons">check</i></span></label>
                    </div>
                    <div>
                        <textarea
                            dir="auto"
                            name="content"
                            placeholder="{$c->__('publish.content_text')}"
                            data-autoheight="true"
                            >{$draft->content ?? ''}</textarea>
                        <label for="desc">{$c->__('publish.content')} <span class="save"><i class="material-icons">check</i></span></label>
                    </div>
                </form>
            </li>
        </ul>
        <ul class="list thin">
            <li>
                <div>
                    <p></p>
                    <p><i class="material-icons">lightbulb</i>{$c->__('publish.help_hashtag')}</p>
                </div>
            </li>
        </ul>
        <ul class="list">
            <li>
                <ul class="list flex card" id="publishembeds">
                    {loop="$draft->embeds"}
                        {autoescape="off"}
                            {$c->prepareEmbed($value)}
                        {/autoescape}
                    {/loop}
                </ul>
            </li>

            <li>
                <div>
                    <a
                        class="button narrow flat icon gray"
                        title="{$c->__('publish.add_link')}"
                        href="#"
                        onclick="Publish_ajaxLink()">
                        <i class="material-icons">link</i>
                    </a>
                    {if="$c->getUser()->hasUpload()"}
                        <a
                            class="button narrow flat icon gray"
                            title="{$c->__('publish.add_snap')}"
                            href="#"
                            onclick="Snap.init()">
                            <i class="material-icons">camera_alt</i>
                        </a>
                        <a
                            class="button narrow flat icon gray"
                            title="{$c->__('draw.title')}"
                            href="#"
                            onclick="Draw.init()">
                            <i class="material-icons">gesture</i>
                        </a>
                        <a
                            class="button narrow flat icon gray"
                            href="#"
                            title="{$c->__('publish.attach')}"
                            onclick="Upload_ajaxRequest()">
                            <i class="material-icons">image</i>
                        </a>
                    {/if}

                    <button class="button send oppose color" onclick="Publish.publish()">
                        <i class="material-icons">send</i>
                        <span class="on_desktop">{$c->__('page.publish')}</span>
                    </button>
                    <button class="button flat oppose gray" onclick="Publish.preview()">
                        <i class="material-icons">visibility</i>
                        <span class="on_desktop">{$c->__('publish.preview')}</span>
                    </button>
                    <button class="button flat oppose gray on_mobile" onclick="PublishHelp_ajaxDrawer()">
                        <i class="material-icons">help</i>
                    </button>
                </div>
            </li>
        </ul>
    </div>
</div>
