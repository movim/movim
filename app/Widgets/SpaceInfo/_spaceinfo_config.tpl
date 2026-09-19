<section class="scroll">
    <form name="spaceinfo_config" oninput="MovimUtils.formSetDirty('spaceinfo_config')">
        <div>
            <ul class="list thick">
                <li class="active" onclick="SpaceInfo_ajaxGetAvatar('{$subscription->server}', '{$subscription->node}'); Drawer.clear('spaceinfo_config');">
                    <span class="primary icon bubble gray">
                        <img src="{$subscription->info->getPicture(placeholder: $subscription->info->name)}">
                    </span>
                    <span class="control icon gray">
                        <i class="material-symbols">edit</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-symbols">chevron_right</i>
                    </span>
                    <div>
                        <p>{$c->__('page.avatar')}</p>
                        <p>{$c->__('button.edit')}</p>
                    </div>
                </li>
            </ul>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">short_text</i>
                    </span>
                    <div>
                        <input id="pubsub#title" name="pubsub#title" label="{$c->__('general.name')}"
                            placeholder="{$c->__('general.name')}" type="text-single"
                            value="{$config['pubsub#title']}">
                        <label for="pubsub#title">{$c->__('general.name')}</label>
                    </div>
                </li>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">notes</i>
                    </span>
                    <div>
                        <textarea id="pubsub#description" name="pubsub#description"
                                data-autoheight="true"
                                placeholder="{$c->__('communityconfig.description')}">{if="isset($config['pubsub#description'])"}{$config['pubsub#description']}{/if}</textarea>
                        <label for="pubsub#description">{$c->__('communityconfig.description')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
    <div id="spaceinfo_affiliations"></div>
    <br />
</section>
<hr />
<footer>
    <button onclick="SpaceInfo_ajaxAskDestroy('{$subscription->server}', '{$subscription->node}'); Drawer.clear('spaceinfo_config');"
        class="button flat">
        {$c->__('button.destroy')}
    </button>
    <button onclick="SpaceInfo_ajaxSetConfig('{$subscription->server}', '{$subscription->node}', MovimUtils.formToJson('spaceinfo_config'));
        SpaceInfo_ajaxSetAffiliations('{$subscription->server}', '{$subscription->node}', MovimUtils.formToJson('spaceinfo_affiliations'));
        Drawer.clear('spaceinfo_config');"
       class="button flat oppose green color">
        {$c->__('button.save')}
    </button>
    <button onclick="Drawer.clear('spaceinfo_config');" class="button flat oppose">
        {$c->__('button.close')}
    </button>
</footer>
