<section class="scroll">
    <form id="config" name="config">
        <h3>{$c->__('communityaffiliation.configuration')}</h3>
        {if="$config"}
        <div>
            <input id="pubsub#title" name="pubsub#title" label="{$c->__('general.name')}" placeholder="{$c->__('general.name')}" type="text-single" value="{$config['pubsub#title']}">
            <label for="pubsub#title">{$c->__('general.name')}</label>
        </div>
        <div>
            <textarea id="pubsub#description" name="pubsub#description" onkeyup="MovimUtils.textareaAutoheight(this);">{$config['pubsub#description']}</textarea>
            <label for="pubsub#description">{$c->__('general.about')}</label>
        </div>
        <div>
            <ul class="list middle labeled">
                <li class="wide">
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#publish_model" value="open"
                                id="publish_model_open" type="radio"
                                {if="$config['pubsub#publish_model'] == 'open'"}checked{/if}>
                            <label for="publish_model_open"></label>
                        </div>
                    </span>
                    <p>Open</p>
                    <p>Everyone can publish</p>
                </li>
                <li class="wide">
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#publish_model" value="publishers"
                                id="publish_model_publishers" type="radio"
                                {if="$config['pubsub#publish_model'] == 'publishers'"}checked{/if}>
                            <label for="publish_model_publishers"></label>
                        </div>
                    </span>
                    <p>Publishers</p>
                    <p>The publishers can publish</p>
                </li>
                <li class="wide">
                    <span class="control">
                        <div class="radio">
                            <input name="pubsub#publish_model" value="subscribers"
                                id="publish_model_subscribers" type="radio"
                                {if="$config['pubsub#publish_model'] == 'subscribers'"}checked{/if}>
                            <label for="publish_model_subscribers"></label>
                        </div>
                    </span>
                    <p>Subscribers</p>
                    <p>The subscribers can publish</p>
                </li>
            </ul>
            <label>Publication</label>
        </div>
        {else}
            {$form}
        {/if}
    </form>
</section>
<div>
    {if="$config"}
        <button class="button flat" onclick="CommunityConfig_ajaxGetConfig('{$server|echapJS}', '{$node|echapJS}', true)">
            <i class="zmdi zmdi-more-vert"></i>
        </button>
    {/if}
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="CommunityConfig_ajaxSetConfig(MovimUtils.formToJson('config'), '{$server|echapJS}', '{$node|echapJS}'); Dialog_ajaxClear();"
       class="button flat">
        {$c->__('button.save')}
    </button>
</div>
