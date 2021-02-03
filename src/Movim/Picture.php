<?php

namespace Movim;

define('DEFAULT_PICTURE_FORMAT', 'jpeg');
define('DEFAULT_PICTURE_QUALITY', 95);
define('DEFAULT_PICTURE_EXPIRATION_HOURS', 12);

class Picture
{
    private $_path = PUBLIC_CACHE_PATH;
    private $_folder = 'cache/';
    private $_key;
    private $_bin  = false;
    private $_formats = ['jpeg' => '.jpg', 'png' => '.png', 'webp' => '.webp'];

    /**
     * @desc Load a bin picture from an URL
     */
    public function fromURL($url)
    {
        $bin = requestURL($url);
        if ($bin) {
            $this->_bin = $bin;
        }
    }

    /**
     * @desc Load a bin picture from a path
     */
    public function fromKey($key)
    {
        return $this->fromPath(
            $this->_path.
            md5($key).
            $this->_formats[DEFAULT_PICTURE_FORMAT]
        );
    }

    /**
     * @desc Load a bin picture from a path
     */
    public function fromPath($path)
    {
        if (file_exists($path)) {
            $size = filesize($path);
            if ($size > 0) {
                $handle = fopen($path, "r");
                $this->_bin = fread($handle, $size);
                fclose($handle);
            }
        }
    }

    /**
     * @desc Load a bin picture from a binary
     */
    public function fromBin($bin = false)
    {
        if ($bin) {
            $this->_bin = (string)$bin;
        }
    }

    /**
     * @desc Return the binary
     */
    public function toBin()
    {
        if ($this->_bin) {
            return $this->_bin;
        }
        return false;
    }

    /**
     * @desc Load a bin picture from a base64
     */
    public function fromBase($base = false)
    {
        if ($base) {
            $this->_bin = (string)base64_decode((string)$base);
        }
    }

    /**
     * @desc Convert to a base64
     */
    public function toBase()
    {
        if ($this->_bin) {
            return base64_encode($this->_bin);
        }
        return false;
    }

    /**
     * @desc check if a picture is old
     */
    public function isOld($key, $format = DEFAULT_PICTURE_FORMAT)
    {
        $original = $this->_path.md5($key).$this->_formats[$format];
        return (!file_exists($original) || (file_exists($original)
             && filemtime($original) < time() - 3600 * DEFAULT_PICTURE_EXPIRATION_HOURS));
    }

    /**
     * @desc Get the original picture URL, without timestamp
     * @param $key The key of the picture
     */
    public function getOriginal($key)
    {
        return $this->get($key, false, false, DEFAULT_PICTURE_FORMAT, true);
    }

    /**
     * @desc Get a picture of the current size
     * @param $key The key of the picture
     * @param $width The width requested
     * @param $height The height requested
     * @return The url of the picture
     */
    public function get($key, $width = false, $height = false, $format = DEFAULT_PICTURE_FORMAT, bool $noTime = false)
    {
        if (!in_array($format, array_keys($this->_formats))) {
            $format = DEFAULT_PICTURE_FORMAT;
        }

        $this->_key = $key;

        $original = $this->_path.md5($this->_key).$this->_formats[$format];

        // We request the original picture
        if ($width == false) {
            if (file_exists($original)) {
                $this->fromPath($original);
                return urilize(
                    $this->_folder . md5($this->_key) . $this->_formats[$format],
                    $noTime
                );
            }
        }

        // We request a specific size
        if (file_exists(
            $this->_path.md5($this->_key) .
            '_' . $width.$this->_formats[$format]
            )
        ) {
            $this->fromPath(
                $this->_path.md5($this->_key) .
                '_' . $width.$this->_formats[$format]
            );

            return urilize(
                $this->_folder.md5($this->_key) .
                '_' . $width.$this->_formats[$format],
                $noTime
            );
        }

        if (file_exists($original)) {
            $this->fromPath($original);
            $this->_createThumbnail($width, $height);

            return urilize(
                $this->_folder.md5($this->_key) .
                '_' . $width . $this->_formats[$format],
                $noTime
            );
        }
    }

    /**
     * @desc Get the current picture size
     * @param $key The picture key
     */
    public function getSize()
    {
        if ($this->_bin) {
            $im = new \Imagick;
            try {
                $im->readImageBlob($this->_bin);
                if ($im != false) {
                    return $im->getImageGeometry();
                }
            } catch (\ImagickException $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * @desc Save a picture (original size)
     * @param $key The key of the picture, if = false return the compressed binary
     */
    public function set($key = false, $format = DEFAULT_PICTURE_FORMAT, $quality = DEFAULT_PICTURE_QUALITY)
    {
        if (!in_array($format, array_keys($this->_formats))) {
            $format = DEFAULT_PICTURE_FORMAT;
        }

        if ($key) {
            $this->_key = $key;
            $path = $this->_path.md5($this->_key).$this->_formats[$format];

            // If the file exist we replace it
            if (file_exists($path) && $this->_bin) {
                @unlink($path);

                // And destroy all the thumbnails
                foreach (
                    glob(
                        $this->_path.
                        md5($key).
                        '*'.$this->_formats[$format],
                        GLOB_NOSORT
                    ) as $path_thumb) {
                    @unlink($path_thumb);
                }
            }
        } else {
            $path = false;
        }

        if ($this->_bin) {
            $im = new \Imagick;
            try {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);

                // Convert the picture to PNG with GD if Imagick doesn't handle WEBP
                if ($finfo->buffer($this->_bin) == 'image/webp'
                    && empty(\Imagick::queryFormats('WEBP'))
                    && array_key_exists('WebP Support', \gd_info())
                    && $path
                ) {
                    $temp = tmpfile();
                    fwrite($temp, $this->_bin);
                    $resource = \imagecreatefromwebp(stream_get_meta_data($temp)['uri']);
                    fclose($temp);

                    \imagepng($resource, $path.'.temp', 0);
                    $this->fromPath($path.'.temp');
                }

                $im->readImageBlob($this->_bin);
                if ($im != false) {
                    $im->setImageFormat($format);

                    if ($format == 'jpeg') {
                        $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $im->setImageAlphaChannel(11);
                        $im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
                        // Put 11 as a value for now, see http://php.net/manual/en/imagick.flattenimages.php#116956
                        //$im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                        $im->setImageBackgroundColor('#ffffff');
                        $im->setImageCompressionQuality($quality);
                        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    }

                    if (empty($im->getImageProperties('png:gAMA'))) {
                        $im->setOption('png:exclude-chunk', 'gAMA');
                    }

                    if ($path) {
                        $im->writeImage($path);
                        $im->clear();
                        return true;
                    }

                    return $im;
                }
                return false;
            } catch (\ImagickException $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * @desc Create a thumbnail of the picture and save it
     * @param $size The size requested
     */
    private function _createThumbnail($width, $height = false, $format = DEFAULT_PICTURE_FORMAT)
    {
        if (!in_array($format, array_keys($this->_formats))) {
            $format = DEFAULT_PICTURE_FORMAT;
        }

        if (!$height) {
            $height = $width;
        }

        $path = $this->_path.md5($this->_key).'_'.$width.$this->_formats[$format];

        $im = new \Imagick;

        try {
            $im->readImageBlob($this->_bin);
            $im->setImageFormat($format);

            if ($format == 'jpeg') {
                $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $im->setImageAlphaChannel(11);
                // Put 11 as a value for now, see http://php.net/manual/en/imagick.flattenimages.php#116956
                //$im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                $im->setImageBackgroundColor('#ffffff');
                $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            }

            $geo = $im->getImageGeometry();

            $im->cropThumbnailImage($width, $height);
            if ($width > $geo['width']) {
                $factor = floor($width/$geo['width']);
                $im->blurImage($factor, 10);
            }

            $im->setImageCompressionQuality(85);
            $im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);

            $im->writeImage($path);
            $im->clear();
        } catch (\ImagickException $e) {
            error_log($e->getMessage());
        }
    }
}
