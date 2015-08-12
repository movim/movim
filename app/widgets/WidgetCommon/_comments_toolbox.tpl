<div class="comments" id="{$post->nodeid}comments">
    {$comments}
    <a 
        class="getcomments" 
        onclick="{$getcomments} this.innerHTML = '<i class=\'fa fa-clock-o\'></i> {$c->__('post.comments_loading')}'"
    >
        <i class="fa fa-comments-o"></i> {$c->__('post.comments_get')}
    </a>
</div>
<div class="comments">
    <a class="addcomment"
        onclick="
        this.parentNode.querySelector('#commentsubmit').style.display = 'table'; 
        this.style.display ='none'">
        <i class="fa fa-comment-o"></i> {$c->__('post.comment_add')}
    </a>
    <table id="commentsubmit">
        <tr>
            <td>
                <textarea 
                    id="{$post->nodeid}commentcontent" 
                    onkeyup="movim_textarea_autoheight(this);"></textarea>
            </td>
        </tr>
        <tr class="commentsubmitrow">
            <td style="width: 100%;"></td>
            <td>
                <a
                    onclick="
                            if(document.getElementById('{$post->nodeid}commentcontent').value != '') {
                                {$publishcomment}
                                document.getElementById('{$post->nodeid}commentcontent').value = '';
                            }"
                    class="button color green"
                >
                    <i class="fa fa-check"></i> {$c->__('button.submit')}
                </a>
            </td>
        </tr>
    </table>
</div>
