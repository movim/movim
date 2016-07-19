<?php

use stojg\crop\CropEntropy;

class Picture {
    private $_path = CACHE_PATH;
    private $_uri  = CACHE_URI;
    private $_key;
    private $_bin  = false;
    private $_formats = ['jpeg' => '.jpg', 'png' => '.png'];

    /**
     * @desc Load a bin picture from an URL
     */
    public function fromURL($url)
    {
        $bin = requestURL($url, 10);
        if($bin) $this->_bin = $bin;
    }

    /**
     * @desc Load a bin picture from a path
     */
    public function fromPath($path)
    {
        $handle = fopen($path, "r");
        $this->_bin = fread($handle, filesize($path));
        fclose($handle);
    }

    /**
     * @desc Load a bin picture from a base64
     */
    public function fromBase($base = false)
    {
        if($base) {
            $this->_bin = (string)base64_decode((string)$base);
        }
    }

    /**
     * @desc Convert to a base64
     */
    public function toBase()
    {
        if($this->_bin)
            return base64_encode($this->_bin);
        else
            return false;
    }

    /**
     * @desc Get a picture of the current size
     * @param $key The key of the picture
     * @param $width The width requested
     * @param $height The height requested
     * @return The url of the picture
     */
    public function get($key, $width = false, $height = false, $format = 'jpeg')
    {
        if(!in_array($format, array_keys($this->_formats))) $format = 'jpeg';
        $this->_key = $key;

        $original = $this->_path.md5($this->_key).$this->_formats[$format];

        // We request the original picture
        if($width == false) {
            if(file_exists($original)) {
                $this->fromPath($original);
                return $this->_uri.md5($this->_key).$this->_formats[$format];
            } else {
                return false;
            }
        // We request a specific size
        } else {
            if(file_exists($this->_path.md5($this->_key).'_'.$width.$this->_formats[$format])) {
                $this->fromPath($this->_path.md5($this->_key).'_'.$width.$this->_formats[$format]);
                return $this->_uri.md5($this->_key).'_'.$width.$this->_formats[$format];
            } else {
                if(file_exists($original)) {
                    $this->fromPath($original);
                    $this->createThumbnail($width, $height);

                    return $this->_uri.md5($this->_key).'_'.$width.$this->_formats[$format];
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * @desc Get the current picture size
     * @param $key The picture key
     */
    public function getSize()
    {
        if($this->_bin) {
            $im = new Imagick();
            try {
                $im->readImageBlob($this->_bin);
                if($im != false) {
                    return $im->getImageGeometry();
                }
            } catch (ImagickException $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * @desc Save a picture (original size)
     * @param $key The key of the picture
     */
    public function set($key, $format = 'jpeg', $quality = 95)
    {
        if(!in_array($format, array_keys($this->_formats))) $format = 'jpeg';

        $this->_key = $key;
        $path = $this->_path.md5($this->_key).$this->_formats[$format];

        // If the file exist we replace it
        if(file_exists($path) && $this->_bin) {
            unlink($path);

            // And destroy all the thumbnails
            foreach(
                glob(
                    $this->_path.
                    md5($key).
                    '*'.$this->_formats[$format],
                    GLOB_NOSORT
                    ) as $path_thumb) {
                unlink($path_thumb);
            }
        }

        if($this->_bin) {
            $im = new Imagick();
            try {
                $im->readImageBlob($this->_bin);
                if($im != false) {
                    $im->setImageFormat($format);

                    if($format == 'jpeg') {
                        $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                        $im->setImageAlphaChannel(11);
                        // Put 11 as a value for now, see http://php.net/manual/en/imagick.flattenimages.php#116956
                        //$im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                        $im->setImageBackgroundColor('#ffffff');
                        $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                    }

                    $im->setImageCompressionQuality($quality);
                    $im->setInterlaceScheme(Imagick::INTERLACE_PLANE);
                    $im->writeImage($path);
                    $im->clear();
                    return true;
                } else {
                    return false;
                }
            } catch (ImagickException $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * @desc Create a thumbnail of the picture and save it
     * @param $size The size requested
     */
    private function createThumbnail($width, $height = false, $format = 'jpeg')
    {
        if(!in_array($format, array_keys($this->_formats))) $format = 'jpeg';
        if(!$height) $height = $width;

        $path = $this->_path.md5($this->_key).'_'.$width.$this->_formats[$format];

        $im = new Imagick;
        $im->readImageBlob($this->_bin);
        $im->setImageFormat($format);

        if($format == 'jpeg') {
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            $im->setImageAlphaChannel(11);
            // Put 11 as a value for now, see http://php.net/manual/en/imagick.flattenimages.php#116956
            //$im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $im->setImageBackgroundColor('#ffffff');
            $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        }

        //$crop = new CropEntropy;
        //$crop->setImage($im);

        $geo = $im->getImageGeometry();

        $im->cropThumbnailImage($width, $height);
        if($width > $geo['width']) {
            $factor = floor($width/$geo['width']);
            $im->blurImage($factor, 10);
        }

        //$im = $crop->resizeAndCrop($width, $height);

        $im->setImageCompressionQuality(85);
        $im->setInterlaceScheme(Imagick::INTERLACE_PLANE);

        $im->writeImage($path);
        $im->clear();
    }
}
