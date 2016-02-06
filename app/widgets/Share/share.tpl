<div id="share_widget">
    <ul class="list thick">
        {if="isset($url)"}
            <li>
                <span class="control icon gray">
                    <i class="zmdi zmdi-link"></i>
                </span>
                <span class="primary icon bubble blue color">
                    <i class="zmdi zmdi-share"></i>
                </span>
                <p>{$c->__('page.share')}</p>
                <p>{$c->__('share.success')}</p>
                <p><a href="{$url}">{$url}</a></p>
            </li>
            <script type="text/javascript">
                localStorage.setItem('share_url', '{$url}');
                movim_redirect('{$c->route('news')}');
            </script>
        {else}
            <li>
                <span class="primary icon bubble orange color">
                    <i class="zmdi zmdi-alert-triangle"></i>
                </span>
                <p>{$c->__('page.share')}</p>
                <p>{$c->__('share.error')}</p>
            </li>
        {/if}
    </ul>
</div>
