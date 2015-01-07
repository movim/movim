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
        >{$c->__('button.close')}</a>
    </div>
</div>

<div class="popup post" id="galleryselect" style="padding: 0;">
    <ul class="thumb">
    {loop="$gallery"}
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
    {if="$gallery == null"}
        <div class="placeholder paddedtop icon media">
            <h1>{$c->__('error.whoops')}</h1>
            <p class="paddedtop">
                {$c->__('error.media_not_found')}<br />
                {$c->__('error.media_ask_upload')}
                
            </p>
            <a class="button color green" href="{$c->route('media')}"><i class="fa fa-folder-open"></i> {$c->__('page.media')}</a>
            <br /><br /><br />
        </div>
    {/if}
    <div class="menu">
        <a 
            class="button color icon no" 
            onclick="movim_toggle_display('#galleryselect');"
        >{$c->__('button.close')}</a>  
    </div>  
</div>
<table id="feedsubmitform">
    <tbody>
        <form name="postpublish" id="postpublish">
            <tr>
                <td>
                    <input name="title" placeholder="{$c->__('post.title')}"/>
                </td>
                <td>
                    <textarea 
                        name="content" 
                        id="postpublishcontent"
                        onkeyup="movim_textarea_autoheight(this);" 
                        placeholder="{$c->__('post.whats_new')}" ></textarea>
                </td>
            </tr>
            <tr id="feedsubmitrow">
                <td>
                    <input type="hidden" id="latlonpos" name="latlonpos"/>
                    <a 
                        title="{$c->__('button.submit')}"
                        href="#" 
                        id="feedmessagesubmit" 
                        onclick="{$publish_item}
                                document.querySelector('#postpublish').reset();
                                movim_textarea_autoheight(document.querySelector('#postpublishcontent'));"
                        class="button color green">
                        <i class="fa fa-envelope"></i> {$c->__('button.submit')}
                    </a>
                    <a 
                        class="button color alone merged left images"
                        style="float: left;"
                        title="{$c->__('page.gallery')}"
                        onclick="
                            movim_toggle_display('#galleryselect');
                            "
                    ><i class="fa fa-picture-o"></i></a>
                    <a 
                        class="button color alone merged left"
                        style="float: left;"
                        title="{$c->__('page.preview')}"
                        onclick="
                            movim_toggle_display('#postpreview');
                            {$post_preview}"
                    ><i class="fa fa-eye"></i></a><a 
                        class="button color alone merged" 
                        style="float: left;"
                        href="http://daringfireball.net/projects/markdown/basics"
                        title="{$c->__('page.help')}"
                        target="_blank"
                    ><i class="fa fa-life-ring"></i></a><a title="{$c->__('post.geolocalisation')}"
                        onclick="setPosition(document.querySelector('#latlonpos')); showPosition(document.querySelector('#latlonpos').value);"
                        style="float: left;"
                        class="button color alone merged right"><i class="fa fa-location-arrow"></i></a>
                    <span id="postpublishlocation"></span>

                </td>
            </tr>
        </form>
    </tbody>
</table>
