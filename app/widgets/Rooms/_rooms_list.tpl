<section class="scroll">
    <ul class="list">
        <li class="subheader">
            <p>
                <span class="info">{$list|count}</span>
                {$c->__('chatrooms.users')}
            </p>
        </li>
        {loop="$list"}
            <li class="{if="$value->last > 60"} inactive{/if}"
                title="{$value->resource}">

                {if="$url = $value->conferencePicture"}
                    <span class="primary icon bubble status {$value->presencekey}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->resource|stringToColor} status {$value->presencekey}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                {if="$value->mucjid != $me"}
                <span class="control icon active gray" onclick="
                    Chats_ajaxOpen('{$value->mucjid}');
                    Chat_ajaxGet('{$value->mucjid}');
                    Dialog_ajaxClear();">
                    <i class="zmdi zmdi zmdi-comment-text-alt"></i>
                </span>
                {/if}
                {if="$value->mucaffiliation =='owner'"}
                    <span class="control icon yellow">
                        <i class="zmdi zmdi-star"></i>
                    </span>
                {/if}
                <p class="line normal">
                    {if="$value->mucjid && strpos($value->mucjid, '/') == false && !$c->supported('anonymous')"}
                        {if="$value->mucjid == $me"}
                            {$value->resource}
                        {else}
                            <a href="{$c->route('contact', $value->mucjid)}">{$value->resource}</a>
                        {/if}
                    {else}
                        {$value->resource}
                    {/if}
                    {if="$value->capability"}
                        <span class="second" title="{$value->capability->name}">
                            <i class="zmdi {$value->capability->getDeviceIcon()}"></i>
                        </span>
                    {/if}
                </p>
                {if="$value->status"}
                    <p class="line">{$value->status}</p>
                {/if}
            </li>
        {/loop}
    </ul>
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
