<section class="scroll">
    <form id="config" name="config">
        {if="$config"}
        <div>
            <ul class="list">
                <li>
                    <div>
                        <input id="pubsub#title" name="pubsub#title" label="{$c->__('general.name')}"
                            placeholder="{$c->__('general.name')}" type="text-single"
                            value="{$config['pubsub#title']}">
                        <label for="pubsub#title">{$c->__('general.name')}</label>
                    </div>
                </li>
                <li>
                    <div>
                        <textarea id="pubsub#description" name="pubsub#description"
                                data-autoheight="true"
                                placeholder="{$c->__('communityconfig.description')}">{if="isset($config['pubsub#description'])"}{$config['pubsub#description']}{/if}</textarea>
                        <label for="pubsub#description">{$c->__('communityconfig.description')}</label>
                    </div>
                </li>
            </ul>
        </div>
        <div>
            <ul class="list middle">
                <li class="subheader">
                    <div>
                        <p>{$c->__('communityconfig.publication')}</p>
                    </div>
                </li>
                <li>
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#publish_model" value="open"
                                id="publish_model_open" type="radio"
                                {if="$config['pubsub#publish_model'] == 'open'"}checked{/if}>
                            <label for="publish_model_open"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('communityconfig.publish_model_open_title')}</p>
                        <p>{$c->__('communityconfig.publish_model_open_text')}</p>
                    </div>
                </li>
                <li>
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#publish_model" value="publishers"
                                id="publish_model_publishers" type="radio"
                                {if="$config['pubsub#publish_model'] == 'publishers'"}checked{/if}>
                            <label for="publish_model_publishers"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('communityconfig.publish_model_publishers_title')}</p>
                        <p>{$c->__('communityconfig.publish_model_publishers_text')}</p>
                    </div>
                </li>
                <li>
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#publish_model" value="subscribers"
                                id="publish_model_subscribers" type="radio"
                                {if="$config['pubsub#publish_model'] == 'subscribers'"}checked{/if}>
                            <label for="publish_model_subscribers"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('communityconfig.publish_model_subscribers_title')}</p>
                        <p>{$c->__('communityconfig.publish_model_subscribers_text')}</p>
                    </div>
                </li>
            </ul>
        </div>
        <div>
            <ul class="list middle">
                <li class="subheader">
                    <div>
                        <p>{$c->__('communityconfig.type')}</p>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">view_agenda</i>
                    </span>
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#type" value="urn:xmpp:pubsub-social-feed:1"
                                id="pubsub_type_feed" type="radio"
                                {if="in_array($config['pubsub#type'], ['urn:xmpp:pubsub-social-feed:0', 'urn:xmpp:pubsub-social-feed:1'])"}checked{/if}>
                            <label for="pubsub_type_feed"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('communityconfig.type_articles_title')}</p>
                        <p>{$c->__('communityconfig.type_articles_text')}</p>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">grid_view</i>
                    </span>
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#type" value="urn:xmpp:pubsub-social-feed:gallery:1"
                                id="pubsub_type_gallery" type="radio"
                                {if="in_array($config['pubsub#type'], ['urn:xmpp:pubsub-social-gallery:0', 'urn:xmpp:pubsub-social-feed:gallery:1'])"}checked{/if}>
                            <label for="pubsub_type_gallery"></label>
                        </div>
                    </span>
                    <div>
                        <p>{$c->__('communityconfig.type_gallery_title')}</p>
                        <p>{$c->__('communityconfig.type_gallery_text')}</p>
                    </div>
                </li>
            </ul>
        </div>
        {else}
            {autoescape="off"}
                {$form}
            {/autoescape}
        {/if}
    </form>
    <br />
</section>
<hr />
<footer>
    {if="$config"}
        <button class="button flat" onclick="CommunityConfig_ajaxGetConfig('{$server|echapJS}', '{$node|echapJS}', true)">
            <i class="material-symbols">more_vert</i>
        </button>
    {else}
        <button class="button flat" onclick="CommunityConfig_ajaxGetConfig('{$server|echapJS}', '{$node|echapJS}', false)">
            <i class="material-symbols">chevron_backward</i>
        </button>
    {/if}
    <button onclick="CommunityConfig_ajaxSetConfig(MovimUtils.formToJson('config'), '{$server|echapJS}', '{$node|echapJS}'); Drawer.clear('community_config');"
       class="button flat oppose green color">
        {$c->__('button.save')}
    </button>
    <button onclick="Drawer.clear('community_config');" class="button flat oppose">
        {$c->__('button.close')}
    </button>
</footer>
