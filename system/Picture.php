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
     * @param $size The size requested
     * @return The url of the picture
     */
    public function get($key, $size = false) {
        $this->_key = $key;

        $original = $this->_path.md5($this->_key).'.jpg';

        // We request the original picture
        if($size == false) {
            if(file_exists($original)) {
                $this->fromPath($original);
                return $this->_uri.md5($this->_key).'.jpg';
            } else {
                return false;
            }
        // We request a specific size
        } else {
            if(file_exists($this->_path.md5($this->_key).'_'.$size.'.jpg')) {
                $this->fromPath($this->_path.md5($this->_key).'_'.$size.'.jpg');
                return $this->_uri.md5($this->_key).'_'.$size.'.jpg';
            } else {
                if(file_exists($original)) {
                    $this->fromPath($original);
                    $this->createThumbnail($size);

                    return $this->_uri.md5($this->_key).'_'.$size.'.jpg';
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
            $source = imagecreatefromstring($this->_bin);
            imagejpeg($source, $path, 95);
            imagedestroy($source);
        }
    }

    /**
     * @desc Create a thumbnail of the picture and save it
     * @param $size The size requested
     */
    private function createThumbnail($size) {
        $path = $this->_path.md5($this->_key).'_'.$size.'.jpg';
        
        $thumb = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);
        
        $source = imagecreatefromstring($this->_bin);
        
        $width = imagesx($source);
        $height = imagesy($source);
        
        if($width >= $height) {
            // For landscape images
            $x_offset = ($width - $height) / 2;
            $y_offset = 0;
            $square_size = $width - ($x_offset * 2);
        } else {
            // For portrait and square images
            $x_offset = 0;
            $y_offset = ($height - $width) / 2;
            $square_size = $height - ($y_offset * 2);
        }
        
        if($source) {
            imagecopyresampled($thumb, $source, 0, 0, $x_offset, $y_offset, $size, $size, $square_size, $square_size);
            imagejpeg($thumb, $path, 95);
            imagedestroy($thumb);
        }
    }
}
