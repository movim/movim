<div class="tools">
    {$c->t("Share with")} : 
    <a
        title="{$c->t("your post will appear in your Movim public feed")}"
        onclick="{$privacy_post_black}" >
        {$c->t("Everyone")}</a>,
    <a
        onclick="{$privacy_post_orange}" >
        {$c->t("Your contacts")}</a><br />
    <a
        style="padding-right: 1em;";
        onclick="
            this.parentNode.querySelector('#deleteyes').style.display = 'inline';
            this.parentNode.querySelector('#deleteno').style.display = 'inline';
            " 
        title="{$c->t("Delete this post")}">
        {$c->t("Delete this post")}
    </a>
    <a
        style="padding-right: 1em; display: none;";
        id="deleteyes"
        onclick="{$delete_post}" >
        ✔ {$c->t("Yes")}
    </a>
    <a
        style="display: none;";
        id="deleteno"
        onclick="
            this.parentNode.querySelector('#deleteyes').style.display = 'none';
            this.style.display = 'none';
            "
        onclick="">
        ✘ {$c->t("No")}
    </a>
</div>
