<div class="tools">
    {$c->__("post.share")} : 
    <a
        title="{$c->t("your post will appear in your Movim public feed")}"
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
        title="{$c->t("Delete this post")}">
        {$c->__('post.delete')}
    </a>
    <a
        style="padding-right: 1em; display: none;";
        id="deleteyes"
        onclick="{$delete_post}" >
        ✔ {$c->__('button.yes')}
    </a>
    <a
        style="display: none;";
        id="deleteno"
        onclick="
            this.parentNode.querySelector('#deleteyes').style.display = 'none';
            this.style.display = 'none';
            "
        onclick="">
        ✘ {$c->__('button.no')}
    </a>
</div>
