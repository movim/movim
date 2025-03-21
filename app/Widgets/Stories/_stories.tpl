<ul class="list">
    <li class="subheader">
        <div>
            <p>{$c->__('stories.title')}</p>
        </div>
    </li>
</ul>
<ul class="list card shadow flex fourth gallery active">
    <li class="block story add" onclick="PublishStories_ajaxOpen()">
        <div>
            <p><i class="material-symbols">add_circle</i></p>
        </div>
    </li>
    {loop="$stories"}
        <li class="block story {if="$value->my_views_count > 0"}seen{/if}" onclick="StoriesViewer_ajaxHttpGet({$value->id})">
            <img class="main" src="{$value->picture->href|protectPicture}">
            <div>
                <p class="line">{$value->title}</p>
                <p class="line">

                    {if="$value->contact"}
                        <span class="icon bubble tiny">
                            <img src="{$value->contact->getPicture()}">
                        </span>
                    {/if}
                    <a href="#" onclick="MovimUtils.reload('{$c->route('contact', $value->aid)}')">
                        {$value->truename}
                    </a>
                </p>
            </div>
        </li>
    {/loop}
</ul>