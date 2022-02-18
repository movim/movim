<?php

namespace Movim;

class Image
{
    private $_key;
    private $_im;
    private $_inMemory = false;

    public static $folder = 'cache/';
    public static $formats = ['jpeg' => '.jpg', 'png' => '.png', 'webp' => '.webp', 'gif' => '.gif'];
    public static $hash = 'sha256'; // Cache need to be cleared in a migration if changed
    private static $originalType = '_o';

    public function __construct()
    {
        $this->_im = new \Imagick;
        $this->_im->setBackgroundColor(new \ImagickPixel('transparent'));
    }

    /**
     * Set the picture $key
     */
    public function setKey(string $key): void
    {
        $this->_key = $key;
    }

    /**
     * Allow save without writing the file to disk
     */
    public function inMemory()
    {
        $this->_inMemory = true;
    }

    /**
     * @desc Load a bin picture from a path
     */
    public function load(string $format = DEFAULT_PICTURE_FORMAT): bool
    {
        if (!empty($this->_key)) {
            return $this->fromPath(
                PUBLIC_CACHE_PATH .
                    hash(Image::$hash, $this->_key) .
                    self::$originalType .
                    self::$formats[$format]
            );
        }

        return false;
    }

    /**
     * @desc Get the current picture geometry
     */
    public function getGeometry(): ?array
    {
        if ($this->_im) {
            return $this->_im->getImageGeometry();
        }
    }

    /**
     * @desc Load a bin picture from an URL
     */
    public function fromURL(string $url):bool
    {
        $bin = requestURL($url);
        if ($bin) {
            return $this->fromBin($bin);
        }

        return false;
    }

    /**
     * @desc Load a bin picture from a base64
     */
    public function fromBase(?string $base = null): bool
    {
        if ($base) {
            return $this->fromBin((string)base64_decode((string)$base));
        }

        return false;
    }

    /**
     * @desc Load a bin picture from a binary
     */
    public function fromBin(?string $bin = null): bool
    {
        if ($bin) {
            try {
                $this->_im->readImageBlob((string)$bin);
                return true;
            } catch (\ImagickException $e) {
                error_log($e->getMessage());
            }
        }

        return false;
    }

    /**
     * @desc Convert to a base64
     */
    public function toBase(): string
    {
        if ($this->_im) {
            return base64_encode($this->_im->getImageBlob());
        }
    }

    /**
     * @desc Return the picture URL or create it if possible
     */
    public static function getOrCreate(string $key, $width = false, $height = false, $format = DEFAULT_PICTURE_FORMAT, bool $noTime = false): ?string
    {
        if (!in_array($format, array_keys(self::$formats))) {
            $format = DEFAULT_PICTURE_FORMAT;
        }

        $type = $width ? '_' . $width
            : self::$originalType;

        /**
         * The file is in the cache and we can directly return it
         */
        if (file_exists(
            PUBLIC_CACHE_PATH . hash(Image::$hash, $key) .
                $type . self::$formats[$format]
        )) {
            return urilize(
                self::$folder . hash(Image::$hash, $key) . $type . self::$formats[$format],
                $noTime
            );
        }

        /**
         * The file is not in the cache but we do have the original to build the requested size
         */
        elseif (
            $width
            && file_exists(
                PUBLIC_CACHE_PATH . hash(Image::$hash, $key) .
                    self::$originalType . self::$formats[$format]
            )
        ) {
            $im = new Image;
            $im->setKey($key);
            if (!$im->load($format)) {
                \Utils::error('Cannot load ' . $key . ' original file');
            }
            $im->save($width, $height, $format);

            return urilize(
                self::$folder . hash(Image::$hash, $key) . $type . self::$formats[$format],
                $noTime
            );
        }

        return null;
    }

    public function save($width = false, $height = false, $format = DEFAULT_PICTURE_FORMAT, $quality = DEFAULT_PICTURE_QUALITY)
    {
        if (!$this->_key && !$this->_inMemory) return;

        $type = $width ? '_' . $width
            : self::$originalType;

        if (!$this->_inMemory) {
            // Cleanup the existing files
            $path = PUBLIC_CACHE_PATH . hash(Image::$hash, $this->_key) . $type . self::$formats[$format];

            // If the file exists we replace it
            if (file_exists($path)) {
                @unlink($path);

                // And destroy all the thumbnails if it's the original
                if ($width == false) {
                    foreach (glob(
                        PUBLIC_CACHE_PATH .
                            hash(Image::$hash, $this->_key) .
                            '*' . self::$formats[$format],
                        GLOB_NOSORT
                    ) as $pathThumb) {
                        @unlink($pathThumb);
                    }
                }
            }
        }

        // Save the file
        try {
            $this->_im = $this->_im->coalesceImages();
            $this->_im->setImageFormat($format);

            if ($format == 'jpeg') {
                $this->_im->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $this->_im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);

                // Put 11 as a value for now, see http://php.net/manual/en/imagick.flattenimages.php#116956
                $this->_im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                //$this->_im->setImageAlphaChannel(11);
                $this->_im->setImageBackgroundColor('#ffffff');
            }

            if ($format == 'webp') {
                $this->_im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);
                $this->_im->setBackgroundColor(new \ImagickPixel('transparent'));
            }

            if ($format == 'jpeg' || $format == 'webp') {
                $this->_im = $this->_im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $this->_im->setImageCompressionQuality($quality);
            }

            if (empty($this->_im->getImageProperties('png:gAMA'))) {
                $this->_im->setOption('png:exclude-chunk', 'gAMA');
            }

            // Auto-rotate
            switch($this->_im->getImageOrientation()) {
                case \Imagick::ORIENTATION_BOTTOMRIGHT:
                    $this->_im->rotateimage("#000", 180);
                    break;

                case \Imagick::ORIENTATION_RIGHTTOP:
                    $this->_im->rotateimage("#000", 90);
                    break;

                case \Imagick::ORIENTATION_LEFTBOTTOM:
                    $this->_im->rotateimage("#000", -90);
                    break;
            }

            $this->_im->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);

            // Resize
            if (!$height) {
                $height = $width;
            }

            if ($width && $height) {
                $geo = $this->_im->getImageGeometry();

                $this->_im->cropThumbnailImage($width, $height);
                if ($width > $geo['width']) {
                    $factor = floor($width / $geo['width']);
                    $this->_im->blurImage($factor, 10);
                }
            }

            if (!$this->_inMemory && $path) {
                $this->_im = $this->_im->deconstructImages();
                $this->_im->writeImages($path, true);
                $this->_im->clear();
            }
        } catch (\ImagickException $e) {
            \Utils::error($e->getMessage());
        }
    }

    /**
     * Get the Imagick image
     */
    public function getImage(): \Imagick
    {
        return $this->_im;
    }

    /**
     * Remove the original
     */
    public function remove(string $format = DEFAULT_PICTURE_FORMAT)
    {
        $path = PUBLIC_CACHE_PATH . hash(Image::$hash, $this->_key) . self::$originalType . self::$formats[$format];

        if (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * @desc Load a bin picture from a path
     */
    private function fromPath(string $path): bool
    {
        if (file_exists($path)) {
            $size = filesize($path);
            if ($size > 0) {
                $handle = fopen($path, "r");
                $bin = fread($handle, $size);
                fclose($handle);

                if ($bin) {
                    return $this->fromBin($bin);
                }
            }
        }

        return false;
    }
}
