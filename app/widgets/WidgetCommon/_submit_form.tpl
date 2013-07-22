<script type="text/javascript">
    function showPosition(poss) {
        {$toggle_position}
    }
    
    function insertImg(url) {
        var cont = document.querySelector('#postpublishcontent');
        cont.value = cont.value + '![](' + url + ')';
        movim_textarea_autoheight(cont);
    }
</script>

<div class="popup post" id="postpreview">
    <div class="content" id="postpreviewcontent">

    </div>
    <div class="menu">
        <a 
            class="button color icon no" 
            onclick="movim_toggle_display('#postpreview');"
        >{$c->t('Close')}</a>
    </div>
</div>

<div class="popup post" id="galleryselect" style="padding: 0;">
    <ul class="thumb">
    {loop="gallery"}
        <li style="background-image: url({$value.thumb});">
            <a 
                href="#" 
                onclick="
                    insertImg('{$value.uri}'); 
                    movim_toggle_display('#galleryselect');">
            </a>
        </li>
    {/loop}
    </ul>
    <div class="menu">
        <a 
            class="button color icon no" 
            onclick="movim_toggle_display('#galleryselect');"
        >{$c->t('Close')}</a>  
    </div>  
</div>
<table id="feedsubmitform">
    <tbody>
        <form name="postpublish" id="postpublish">
            <tr>
                <td>
                    <textarea 
                        name="content" 
                        id="postpublishcontent"
                        onkeyup="movim_textarea_autoheight(this);" 
                        placeholder="{$c->t("What's new ?")}" ></textarea>
                </td>
            </tr>
            <tr id="feedsubmitrow">
                <td>
                    <input type="hidden" id="latlonpos" name="latlonpos"/>
                    <a 
                        title="{$c->t("Submit")}"
                        href="#" 
                        id="feedmessagesubmit" 
                        onclick="{$publish_item}
                                document.querySelector('#postpublish').reset();
                                movim_textarea_autoheight(document.querySelector('#postpublishcontent'));"
                        class="button icon color green icon yes">
                        {$c->t("Submit")}
                    </a>
                    <a 
                        class="button icon color alone merged left images"
                        style="float: left;"
                        title="{$c->t('Gallery')}"
                        onclick="
                            movim_toggle_display('#galleryselect');
                            "
                    ></a>
                    <a 
                        class="button icon color alone merged left preview"
                        style="float: left;"
                        title="{$c->t('Preview')}"
                        onclick="
                            movim_toggle_display('#postpreview');
                            {$post_preview}"
                    ></a>

                    <!--<a 
                        title="Plus"
                        href="#"
                        id="postpublishsize"
                        onclick="frameHeight(this, document.querySelector(\'#postpublishcontent\'));"
                        style="float: left;"
                        class="button color icon alone add merged"
                    ></a>--><a 
                        class="button color icon alone help merged" 
                        style="float: left;"
                        href="http://daringfireball.net/projects/markdown/basics"
                        target="_blank"
                    ></a><a title="{$c->t("Geolocalisation")}"
                        onclick="setPosition(document.querySelector('#latlonpos'));"
                        style="float: left;"
                        class="button icon color icon alone geo merged right"></a>
                    <span id="postpublishlocation"></span>

                </td>
            </tr>
        </form>
    </tbody>
</table>
