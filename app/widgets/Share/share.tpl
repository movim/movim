<div id="share_widget">
    <ul class="thick">
        {if="isset($url)"}
            <li class="condensed action">
                <div class="action">
                    <i class="md md-link"></i>
                </div>
                <span class="icon bubble blue color">
                    <i class="md md-share"></i>
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
                    <i class="md md-error"></i>
                </span>
                <span>{$c->__('page.share')}</span>
                <p>{$c->__('share.error')}</p>
            </li>
        {/if}
    </ul>
</div>
