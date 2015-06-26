<div id="share_widget">
    <ul class="thick">
        {if="isset($url)"}
            <li class="condensed action">
                <div class="action">
                    <i class="zmdi zmdi-link"></i>
                </div>
                <span class="icon bubble blue color">
                    <i class="zmdi zmdi-share"></i>
                </span>
                <span>{$c->__('page.share')}</span>
                <p>{$c->__('share.success')}</p>
                <p><a href="{$url}">{$url}</a></p>
            </li>
            <script type="text/javascript">
                localStorage.setItem('share_url', '{$url}');
                movim_redirect('{$c->route('news')}');
            </script>
        {else}
            <li class="condensed">
                <span class="icon bubble orange color">
                    <i class="zmdi zmdi-error"></i>
                </span>
                <span>{$c->__('page.share')}</span>
                <p>{$c->__('share.error')}</p>
            </li>
        {/if}
    </ul>
</div>
