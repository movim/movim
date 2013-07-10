<script type="text/javascript">
    function showPosition(poss) {
        {$toggle_position}
    }
</script>

<div class="popup post" id="postpreview">
    <div class="content" id="postpreviewcontent">

    </div>
    <a 
        class="button color icon no" 
        onclick="movim_toggle_display('#postpreview');"
    >{$c->t('Close')}</a>
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
