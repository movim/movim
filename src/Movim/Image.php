<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

class Image
{
    private $_key;
    private $_im;
    private $_inMemory = false;

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
    public function getKey(): string
    {
        return $this->_key;
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
    public function load(string $format = DEFAULT_PICTURE_FORMAT, ?string $directory = CACHE_DIR): bool
    {
        if (!empty($this->_key)) {
            return $this->fromPath(
                PUBLIC_PATH . $directory .
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

        return null;
    }

    /**
     * @desc Load a bin picture from a path
     */
    public function fromPath(string $path): bool
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

    /**
     * @desc Load a bin picture from a base64
     */
    public function fromBase64(?string $base = null): bool
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
    public function toBase(): ?string
    {
        if ($this->_im) {
            return base64_encode($this->_im->getImageBlob());
        }

        return null;
    }

    /**
     * @desc Return the picture URL or create it if possible
     */
    public static function getOrCreate(
        string $key,
        ?int $width = null,
        ?int $height = null,
        ?string $format = DEFAULT_PICTURE_FORMAT,
        ?bool $noTime = false,
        ?string $directory = CACHE_DIR,
    ): ?string {
        if (!in_array($format, array_keys(self::$formats))) {
            $format = DEFAULT_PICTURE_FORMAT;
        }

        $type = $width != null
            ? '_' . $width
            : self::$originalType;

        /**
         * The file is in the cache and we can directly return it
         */
        if (file_exists(
            PUBLIC_PATH . $directory . hash(Image::$hash, $key) .
                $type . self::$formats[$format]
        )) {
            return urilize(
                $directory . hash(Image::$hash, $key) . $type . self::$formats[$format],
                $noTime
            );
        }

        /**
         * The file is not in the cache but we do have the original to build the requested size
         */
        elseif (
            $width != null
            && file_exists(
                PUBLIC_PATH . $directory . hash(Image::$hash, $key) .
                    self::$originalType . self::$formats[$format]
            )
        ) {
            $im = new Image;
            $im->setKey($key);
            if (!$im->load($format)) {
                logError('Cannot load ' . $key . ' original file');
            }
            $im->save($width, $height, $format);

            return urilize(
                $directory . hash(Image::$hash, $key) . $type . self::$formats[$format],
                $noTime
            );
        }

        return null;
    }

    public function save(
        ?int $width = null,
        ?int $height = null,
        ?string $format = DEFAULT_PICTURE_FORMAT,
        ?int $quality = DEFAULT_PICTURE_QUALITY,
        ?string $directory = CACHE_DIR
    ) {
        if (!$this->_key && !$this->_inMemory) return;

        $type = $width != null ? '_' . $width
            : self::$originalType;

        if (!$this->_inMemory) {
            // Cleanup the existing files
            $path = PUBLIC_PATH . $directory . hash(Image::$hash, $this->_key) . $type . self::$formats[$format];

            // If the file exists we replace it
            if (file_exists($path)) {
                @unlink($path);

                // And destroy all the thumbnails if it's the original
                if ($width == false) {
                    foreach (
                        glob(
                            PUBLIC_PATH . $directory .
                                hash(Image::$hash, $this->_key) .
                                '*' . self::$formats[$format],
                            GLOB_NOSORT
                        ) as $pathThumb
                    ) {
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
                $this->_im->setImageAlphaChannel(\Imagick::getVersion()['versionNumber'] >= 1808
                    ? \Imagick::ALPHACHANNEL_OFF
                    : \Imagick::ALPHACHANNEL_REMOVE);
                $this->_im->setImageBackgroundColor('#ffffff');
            }

            if ($format == 'webp') {
                $this->_im->setImageAlphaChannel(\Imagick::getVersion()['versionNumber'] >= 1808
                    ? \Imagick::ALPHACHANNEL_ON
                    : \Imagick::ALPHACHANNEL_ACTIVATE);
                $this->_im->setBackgroundColor(new \ImagickPixel('transparent'));
            }

            if ($format == 'jpeg' || $format == 'webp') {
                if ($this->_im->getNumberImages() == 1) {
                    $this->_im = $this->_im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                }

                $this->_im->setImageCompressionQuality($quality);
            }

            if (empty($this->_im->getImageProperties('png:gAMA'))) {
                $this->_im->setOption('png:exclude-chunk', 'gAMA');
            }

            // Auto-rotate
            switch ($this->_im->getImageOrientation()) {
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
            if ($height == null) {
                $height = $width;
            }

            if ($width != null && $height != null) {
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
            logError($this->_key . ' ' . $e->getMessage());
        }
    }

    /**
     * Get the Imagick image
     */
    public function getImage(): \Imagick
    {
        return $this->_im;
    }
}
