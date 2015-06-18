<?php

class Picture {
    private $_path = CACHE_PATH;
    private $_uri  = CACHE_URI;
    private $_key;
    private $_bin  = false;

    /**
     * @desc Load a bin picture from a path
     */
    public function fromPath($path) {
        $handle = fopen($path, "r");
        $this->_bin = fread($handle, filesize($path));
        fclose($handle);
    }

    /**
     * @desc Load a bin picture from a base64
     */
    public function fromBase($base = false) {
        if($base) {
            $this->_bin = (string)base64_decode((string)$base);
        }
    }

    /**
     * @desc Convert to a base64
     */
    public function toBase() {
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
    public function get($key, $width = false, $height = false) {
        $this->_key = $key;

        $original = $this->_path.md5($this->_key).'.jpg';

        // We request the original picture
        if($width == false) {
            if(file_exists($original)) {
                $this->fromPath($original);
                return $this->_uri.md5($this->_key).'.jpg';
            } else {
                return false;
            }
        // We request a specific size
        } else {
            if(file_exists($this->_path.md5($this->_key).'_'.$width.'.jpg')) {
                $this->fromPath($this->_path.md5($this->_key).'_'.$width.'.jpg');
                return $this->_uri.md5($this->_key).'_'.$width.'.jpg';
            } else {
                if(file_exists($original)) {
                    $this->fromPath($original);
                    $this->createThumbnail($width, $height);

                    return $this->_uri.md5($this->_key).'_'.$width.'.jpg';
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * @desc Save a picture (original size)
     * @param $key The key of the picture
     */
    public function set($key) {
        $this->_key = $key;
        $path = $this->_path.md5($this->_key).'.jpg';

        // If the file exist we replace it
        if(file_exists($path) && $this->_bin) {
            unlink($path);

            // And destroy all the thumbnails
            foreach(
                glob(
                    $this->_path.
                    md5($key).
                    '*.jpg',
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
                    $im->setImageCompressionQuality(95);
                    $im->setInterlaceScheme(Imagick::INTERLACE_PLANE);
                    $im->writeImage($path);
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
    private function createThumbnail($width, $height = false) {
        if(!$height) $height = $width;
        
        $path = $this->_path.md5($this->_key).'_'.$width.'.jpg';

        $im = new Imagick;
        $im->readImageBlob($this->_bin);

        $geo = $im->getImageGeometry();

        $im->cropThumbnailImage($width, $height);
        if($width > $geo['width']) {
            $factor = floor($width/$geo['width']);
            $im->blurImage($factor, 10);
        }

        $im->setImageCompressionQuality(85);
        $im->setInterlaceScheme(Imagick::INTERLACE_PLANE);
        
        $im->writeImage($path);
        
    }
}
