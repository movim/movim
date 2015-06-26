<div class="tools">
    {$c->__("post.share")} :
    <a
        onclick="{$privacy_post_black}" >
        {$c->__('post.share_everyone')}</a>,
    <a
        onclick="{$privacy_post_orange}" >
        {$c->__('post.share_your_contacts')}</a><br />
    <a
        style="padding-right: 1em;";
        onclick="
            this.parentNode.querySelector('#deleteyes').style.display = 'inline';
            this.parentNode.querySelector('#deleteno').style.display = 'inline';
            "
        title="{$c->__('post.delete')}">
        {$c->__('post.delete')}
    </a>
    <a
        style="padding-right: 1em; display: none;";
        id="deleteyes"
        onclick="{$delete_post}" >
        ✔ {$c->__('button.bool_yes')}
    </a>
    <a
        style="display: none;";
        id="deleteno"
        onclick="
            this.parentNode.querySelector('#deleteyes').style.display = 'none';
            this.style.display = 'none';
            ">
        ✘ {$c->__('button.bool_no')}
    </a>
</div>
