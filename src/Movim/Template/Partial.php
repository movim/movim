<?php

namespace Movim\Template;

use App\User;
use Rain\Tpl;
use Movim\Widget\Base;

class Partial extends Tpl
{
    private $extension = '.rtpl.cache';

    public function __construct(Base $widget)
    {
        $this->objectConfigure([
            'tpl_dir'       => APP_PATH.'widgets/'.$widget->getName().'/',
            'cache_dir'     => CACHE_PATH,
            'tpl_ext'       => 'tpl',
            'auto_escape'   => true
        ]);

        $this->assign('c', $widget);
    }

    public function draw($templateFilePath, $bool = true)
    {
        return parent::draw($templateFilePath, true);
    }

    public function cache(string $templateFilePath, string $key)
    {
        $compiled = parent::draw($templateFilePath, true);
        file_put_contents($this->resolvedCacheKey($templateFilePath, $key), gzcompress($compiled));

        return $compiled;
    }

    public function cacheClear(string $templateFilePath, string $key = null)
    {
        if ($key) {
            $path = $this->resolvedCacheKey($templateFilePath, $key);

            if (file_exists ($path)) {
                @unlink($path);
            }
        } else {
            foreach (
                glob(
                    CACHE_PATH.
                    sha1(User::me()->id) .
                    '_' .
                    $templateFilePath .
                    '_' .
                    '*'.
                    $this->extension,
                    GLOB_NOSORT
                ) as $path) {
                @unlink($path);
            }
        }
    }

    public function cached(string $templateFilePath, string $key)
    {
        $path = $this->resolvedCacheKey($templateFilePath, $key);
        if (file_exists ($path)) {
            return gzuncompress(file_get_contents($path));
        }

        return false;
    }

    private function resolvedCacheKey(string $templateFilePath, string $key): string
    {
        return CACHE_PATH . sha1(User::me()->id) . '_' . $templateFilePath . '_' . cleanupId($key). $this->extension;
    }
}
