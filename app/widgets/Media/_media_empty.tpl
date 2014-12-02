<div class="placeholder paddedtop icon media">
    <h1>{$c->__('error.whoops')}</h1>
    <p class="paddedtop">
        {$c->__('error.media_not_found')}
    </p>
    <a class="button color green" href="{$c->route('media', null, 'mediaupload')}"><i class="fa fa-upload"></i> {$c->__('button.upload')}</a>
</div>
