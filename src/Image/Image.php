<?php

namespace Selective\Image;

use InvalidArgumentException;
use RuntimeException;

/**
 * Image Class.
 */
class Image
{
    /**
     * @var resource
     */
    private $image;

    /**
     * Save image object as file.
     *
     * @param string $fileName The output file
     * @param int $quality The image quality in percent (0-100)
     * @param int $bit 24 or 16 bit (bmp only)
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public function save(string $fileName, int $quality = 100, int $bit = 24): self
    {
        if ($quality < 0 || $quality > 100) {
            throw new InvalidArgumentException('The image quality must be between 0 and 100.');
        }

        $this->validateImageResource($this->image);
        $extension = $this->getImageExtension($fileName);

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($this->image, $fileName, $quality);
                break;
            case 'gif':
                imagegif($this->image, $fileName);
                break;
            case 'png':
                imagepng($this->image, $fileName, $this->getPngCompressionLevel($quality));
                break;
            case 'bmp':

                   file_put_contents(
                        $fileName,
                        $bit === 16 ? $this->convertImageToBmp16($this->image) : $this->convertImageToBmp24($this->image)
                    );
                    break;

            default:
                throw new InvalidArgumentException(sprintf('Image format not supported: %s', $extension));
        }

        return $this;
    }

    /**
     * Convert percent to png compresion level from 0 (no compression = 100%) to 9 (max compression = 0%).
     *
     * @param int $percent percent (higher is better quality)
     *
     * @return int png compression level (lower is better quality)
     */
    private function getPngCompressionLevel(int $percent): int
    {
        // Round percent
        $percent = $percent === 0 ? 0 : $percent < 5 ? 10 : round($percent / 10) * 10;

        return (int)round(($percent * 9) / 100);
    }

    /**
     * Get image extension.
     *
     * @param string $fileName
     *
     * @return string The image type
     */
    private function getImageExtension(string $fileName): string
    {
        $exifImageLists = [
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_BMP => 'bmp',
            IMAGETYPE_PNG => 'png',
        ];

        $extension = null;

        if (function_exists('exif_imagetype') && file_exists($fileName)) {
            $imageType = exif_imagetype($fileName);

            if (isset($exifImageLists[$imageType])) {
                $extension = $exifImageLists[$imageType];
            }
        }

        if ($extension === null) {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        }

        return $extension;
    }

    /**
     * Convert image resource to bmp format (24 bit).
     *
     * @param resource $image
     *
     * @throws RuntimeException
     *
     * @return string $result binary data
     */
    private function convertImageToBmp24(&$image): string
    {
        $this->validateImageResource($image);

        $width = imagesx($image);
        $height = imagesy($image);
        $result = '';

        if (!imageistruecolor($image)) {
            $tmp = imagecreatetruecolor($width, $height);
            imagecopy($tmp, $image, 0, 0, 0, 0, $width, $height);
            imagedestroy($image);
            $image = &$tmp;
        }

        $biBPLine = $width * 3;
        $biStride = ($biBPLine + 3) & ~3;
        $biSizeImage = $biStride * $height;
        $bfOffBits = 54;
        $bfSize = $bfOffBits + $biSizeImage;

        $result .= substr('BM', 0, 2);
        $result .= pack('VvvV', $bfSize, 0, 0, $bfOffBits);
        $result .= pack('VVVvvVVVVVV', 40, $width, $height, 1, 24, 0, $biSizeImage, 0, 0, 0, 0);

        $numpad = $biStride - $biBPLine;
        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $col = imagecolorat($image, $x, $y);
                $result .= substr(pack('V', $col), 0, 3);
            }
            for ($i = 0; $i < $numpad; $i++) {
                $result .= pack('C', 0);
            }
        }

        return $result;
    }

    /**
     * Convert image resource to bmp format (16 bit).
     *
     * @param resource $im Image resource
     *
     * @throws RuntimeException
     *
     * @return string $result binary data
     */
    private function convertImageToBmp16(&$im): string
    {
        $this->validateImageResource($im);

        $width = imagesx($im);
        $height = imagesy($im);
        $result = '';

        if (!imageistruecolor($im)) {
            $tmp = imagecreatetruecolor($width, $height);
            imagecopy($tmp, $im, 0, 0, 0, 0, $width, $height);
            imagedestroy($im);
            $im = &$tmp;
        }

        $biBPLine = $width * 2;
        $biStride = ($biBPLine + 3) & ~3;
        $biSizeImage = $biStride * $height;
        $bfOffBits = 66;
        $bfSize = $bfOffBits + $biSizeImage;
        $result .= substr('BM', 0, 2);
        $result .= pack('VvvV', $bfSize, 0, 0, $bfOffBits);
        $result .= pack('VVVvvVVVVVV', 40, $width, '-' . $height, 1, 16, 3, $biSizeImage, 0, 0, 0, 0);
        $numpad = $biStride - $biBPLine;

        //for ($y = $h - 1; $y >= 0; --$y) {
        $result .= pack('VVV', 63488, 2016, 31);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($im, $x, $y);
                $r24 = ($rgb >> 16) & 0xFF;
                $g24 = ($rgb >> 8) & 0xFF;
                $b24 = $rgb & 0xFF;
                $col = ((($r24 >> 3) << 11) | (($g24 >> 2) << 5) | ($b24 >> 3));
                $result .= pack('v', $col);
            }
            for ($i = 0; $i < $numpad; $i++) {
                $result .= pack('C', 0);
            }
        }

        return $result;
    }

    /**
     * Validate image resource.
     *
     * @param resource|mixed $image Image
     *
     * @throws RuntimeException
     *
     * @return bool True
     */
    private function validateImageResource($image): bool
    {
        if (empty($image) || !is_resource($image) || get_resource_type($image) !== 'gd') {
            throw new RuntimeException('Image must be a valid image resource');
        }

        return true;
    }

    /**
     * Returns image from string.
     *
     * @param string $data String containing the image data
     *
     * @return self
     */
    public static function createFromString(string $data): self
    {
        $resource = imagecreatefromstring($data);

        $image = new self();
        $image->image = $resource;

        return $image;
    }

    /**
     * Returns image from string.
     *
     * @param string $filename
     *
     * @return self
     */
    public static function createFromFile(string $filename): self
    {
        return static::createFromString(file_get_contents($filename));
    }

    /**
     * Returns image from a resource.
     *
     * @param resource $resource The image resource
     *
     * @return self
     */
    public static function createFromResource($resource): self
    {
        $image = new self();
        $image->validateImageResource($resource);

        $image->image = $resource;

        return $image;
    }

    /**
     * Add watermark to image.
     *
     * @param string $watermarkFile watermark image filename
     * @param array $params optional parameters
     *
     * @return self
     */
    public function watermark(string $watermarkFile, array $params = []): self
    {
        $width = $params['w'] ?? 1024;
        $height = $params['h'] ?? null;
        $sharpen = $params['sharpen'] ?? false;
        $topPercent = $params['top_percent'] ?? 5;
        $leftPercent = $params['left_percent'] ?? 5;

        $imageWatermark = $this->getImageResource($watermarkFile);
        $imageBackground = $this->image;

        $imageBackground = $this->resizeImage($imageBackground, $width, $height, false);

        $srcW = imagesx($imageBackground);
        $srcH = imagesy($imageBackground);

        $srcPngW = imagesx($imageWatermark);
        $srcPngH = imagesy($imageWatermark);

        $dstW = $srcW;
        $dstH = $srcH;

        $dstPngW = $srcW / 3;
        $dstPngH = (int)($srcPngH * $dstPngW / $srcPngW);

        $imgToLeft = ($dstW / 100) * $leftPercent;
        $imgToTop = ($dstH / 100) * $topPercent;

        $out = imagecreatetruecolor($dstW, $dstH);

        // 1. layer
        imagecopyresampled($out, $imageBackground, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        // Append second layer (transparent png)
        imagecopyresampled($out, $imageWatermark, $imgToLeft, $imgToTop, 0, 0, $dstPngW, $dstPngH, $srcPngW, $srcPngH);

        if ($sharpen === true) {
            $amount = 50;
            $radius = 0.5;
            $threshold = 3;
            $out = $this->unsharpMask($out, $amount, $radius, $threshold);
        }

        $this->image = $out;

        return $this;
    }

    /**
     * Returns image resource by filename.
     *
     * @param string $fileName
     *
     * $this->getImage($sourceFile)
     *
     * @return resource
     */
    private function getImageResource(string $fileName)
    {
        if (!file_exists($fileName)) {
            throw new RuntimeException(sprintf('File not found: %s', $fileName));
        }

        $image = null;
        $size = getimagesize($fileName);

        switch ($size['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($fileName);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($fileName);
                break;
            case 'image/png':
                $image = imagecreatefrompng($fileName);
                break;
            case 'image/bmp':
            case 'image/x-ms-bmp':
                $image = $this->createFromBmp($fileName);
                break;
        }

        $this->validateImageResource($image);

        return $image;
    }

    /**
     * Create image resource from bmp file.
     *
     * @param string $fileName
     *
     * @throws RuntimeException
     *
     * @return resource The image resource
     */
    private function createFromBmp(string $fileName)
    {
        // open the file in binary mode
        if (!$f1 = @fopen($fileName, 'rb')) {
            throw new RuntimeException(sprintf('File could not be opened: %s', $fileName));
        }

        // load file header
        $file = unpack('vfile_type/Vfile_size/Vreserved/Vbitmap_offset', fread($f1, 14));
        if ($file['file_type'] != 19778) {
            throw new RuntimeException(sprintf('Invalid BMP file type: %s', $fileName));
        }

        // load bmp headers
        $bmp = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
            '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
            '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
        $bmp['colors'] = pow(2, $bmp['bits_per_pixel']);
        if ($bmp['size_bitmap'] == 0) {
            $bmp['size_bitmap'] = $file['file_size'] - $file['bitmap_offset'];
        }
        $bmp['bytes_per_pixel'] = $bmp['bits_per_pixel'] / 8;
        $bmp['bytes_per_pixel2'] = ceil($bmp['bytes_per_pixel']);
        $bmp['decal'] = ($bmp['width'] * $bmp['bytes_per_pixel'] / 4);
        $bmp['decal'] -= floor($bmp['width'] * $bmp['bytes_per_pixel'] / 4);
        $bmp['decal'] = 4 - (4 * $bmp['decal']);
        if ($bmp['decal'] == 4) {
            $bmp['decal'] = 0;
        }

        // load color palette
        $palette = [];
        if ($bmp['colors'] < 16777216) {
            $palette = unpack('V' . $bmp['colors'], fread($f1, $bmp['colors'] * 4));
        }

        // create image
        $img = fread($f1, $bmp['size_bitmap']);
        $vide = chr(0);

        $res = imagecreatetruecolor($bmp['width'], $bmp['height']);
        $p = 0;
        $y = $bmp['height'] - 1;

        while ($y >= 0) {
            $x = 0;
            while ($x < $bmp['width']) {
                if ($bmp['bits_per_pixel'] == 24) {
                    $color = unpack('V', substr($img, $p, 3) . $vide);
                } elseif ($bmp['bits_per_pixel'] == 16) {
                    $color = unpack('n', substr($img, $p, 2));
                    $color[1] = $palette[$color[1] + 1];
                } elseif ($bmp['bits_per_pixel'] == 8) {
                    $color = unpack('n', $vide . substr($img, $p, 1));
                    $color[1] = $palette[$color[1] + 1];
                } elseif ($bmp['bits_per_pixel'] == 4) {
                    $color = unpack('n', $vide . substr($img, (int)floor($p), 1));
                    if (($p * 2) % 2 == 0) {
                        $color[1] = ($color[1] >> 4);
                    } else {
                        $color[1] = ($color[1] & 0x0F);
                    }
                    $color[1] = $palette[$color[1] + 1];
                } elseif ($bmp['bits_per_pixel'] == 1) {
                    $color = unpack('n', $vide . substr($img, (int)floor($p), 1));
                    if (($p * 8) % 8 == 0) {
                        $color[1] = $color[1] >> 7;
                    } elseif (($p * 8) % 8 == 1) {
                        $color[1] = ($color[1] & 0x40) >> 6;
                    } elseif (($p * 8) % 8 == 2) {
                        $color[1] = ($color[1] & 0x20) >> 5;
                    } elseif (($p * 8) % 8 == 3) {
                        $color[1] = ($color[1] & 0x10) >> 4;
                    } elseif (($p * 8) % 8 == 4) {
                        $color[1] = ($color[1] & 0x8) >> 3;
                    } elseif (($p * 8) % 8 == 5) {
                        $color[1] = ($color[1] & 0x4) >> 2;
                    } elseif (($p * 8) % 8 == 6) {
                        $color[1] = ($color[1] & 0x2) >> 1;
                    } elseif (($p * 8) % 8 == 7) {
                        $color[1] = ($color[1] & 0x1);
                    }
                    $color[1] = $palette[$color[1] + 1];
                } else {
                    throw new RuntimeException(sprintf('Invalid BMP header: %s', $fileName));
                }
                imagesetpixel($res, $x, $y, $color[1]);
                $x++;
                $p += $bmp['bytes_per_pixel'];
            }
            $y--;
            $p += $bmp['decal'];
        }

        // close the file
        fclose($f1);

        return $res;
    }

    /**
     * Resize image resource.
     *
     * @param resource $image
     * @param int $width
     * @param int $height
     * @param bool $sharpen
     *
     * @return resource
     */
    private function resizeImage($image, int $width, int $height = null, bool $sharpen = true)
    {
        $widthOrig = imagesx($image);
        $heightOrig = imagesy($image);

        if ($width > $widthOrig) {
            $height = $heightOrig;
            $width = $widthOrig;
        }

        if ($height === null) {
            // proportional resize
            $height = (int)($heightOrig * $width / $widthOrig);
        }

        // Resample
        $imageP = imagecreatetruecolor($width, $height);
        $this->copyImageResampled($imageP, $image, 0, 0, 0, 0, $width, $height, $widthOrig, $heightOrig, 3);

        if ($sharpen === true) {
            $amount = 50;
            $radius = 0.5;
            $threshold = 3;
            $imageP = $this->unsharpMask($imageP, $amount, $radius, $threshold);
        }

        return $imageP;
    }

    /**
     * Plug-and-Play copyImageResampled function replaces much slower imagecopyresampled.
     *
     * Just include this function and change all "imagecopyresampled" references to "copyImageResampled".
     *
     * Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
     * Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
     *
     * Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
     *
     * Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
     * 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
     * 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
     * 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
     * 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
     * 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
     *
     * @param resource $dstImage
     * @param resource $srcImage
     * @param int $dstX
     * @param int $dstY
     * @param int $srcX
     * @param int $srcY
     * @param int $dstW
     * @param int $dstH
     * @param int $srcW
     * @param int $srcH
     * @param int $quality default = 3 (range 1-?)
     *
     * @return bool success
     */
    private function copyImageResampled(&$dstImage, &$srcImage, int $dstX, int $dstY, int $srcX, int $srcY, int $dstW, int $dstH, int $srcW, int $srcH, int $quality = 3): bool
    {
        $this->validateImageResource($dstImage);
        $this->validateImageResource($srcImage);

        if ($quality <= 0) {
            throw new RuntimeException(sprintf('Invalid quality: %s', $quality));
        }

        if ($quality < 5 && (($dstW * $quality) < $srcW || ($dstH * $quality) < $srcH)) {
            $temp = imagecreatetruecolor($dstH * $quality + 1, $dstH * $quality + 1);
            imagecopyresized($temp, $srcImage, 0, 0, $srcX, $srcY, $dstH * $quality + 1, $dstH * $quality + 1, $srcW, $srcH);
            imagecopyresampled($dstImage, $temp, $dstX, $dstY, 0, 0, $dstW, $dstH, $dstW * $quality, $dstH * $quality);
            imagedestroy($temp);
        } else {
            imagecopyresampled($dstImage, $srcImage, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
        }

        return true;
    }

    /**
     * Resize image.
     *
     * @param int $width
     * @param int $height
     * @param bool $sharpen
     *
     * @return self
     */
    public function resize(int $width, int $height = null, bool $sharpen = true): self
    {
        $this->image = $this->resizeImage($this->image, $width, $height, $sharpen);

        return $this;
    }

    /**
     * Unsharp Mask for PHP - version 2.1.1
     * Unsharp mask algorithm by Torstein Hansi 2003-07.
     * thoensi_at_netcom_dot_no.
     * Please leave this notice.
     *
     * New:
     * - In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements.
     * - From version 2 (July 17 2006) the script uses the imageconvolution function in PHP
     * version >= 5.1, which improves the performance considerably.
     * http://vikjavev.no/computing/ump.php
     *
     * Unsharp masking is a traditional darkroom technique that has proven very suitable for
     * digital imaging. The principle of unsharp masking is to create a blurred copy of the image
     * and compare it to the underlying original. The difference in colour values
     * between the two images is greatest for the pixels near sharp edges. When this
     * difference is subtracted from the original image, the edges will be accentuated.
     *
     * The Amount parameter simply says how much of the effect you want. 100 is 'normal'.
     * Radius is the radius of the blurring circle of the mask. 'Threshold' is the least
     * difference in colour values that is allowed between the original and the mask. In practice
     * this means that low-contrast areas of the picture are left unrendered whereas edges
     * are treated normally. This is good for pictures of e.g. skin or blue skies.
     *
     * Any suggenstions for improvement of the algorithm, expecially regarding the speed
     * and the roundoff errors in the Gaussian blur process, are welcome.
     * Amount: 80 (typically 50 - 200)
     * Radius: 0.5 (typically 0.5 - 1)
     * Threshold: 3 (typically 0 - 5)
     *
     * @param resource $image an truecolor image resource
     * @param float $amount (0-500)
     * @param float $radius (0-50)
     * @param int $threshold (0-255)
     *
     * @return resource
     */
    private function unsharpMask($image, float $amount, float $radius, int $threshold)
    {
        // Attempt to calibrate the parameters to photoshop
        if ($amount > 500) {
            $amount = 500;
        }
        $amount *= 0.016;
        if ($radius > 50) {
            $radius = 50;
        }
        $radius *= 2;
        if ($threshold > 255) {
            $threshold = 255;
        }
        // Only integers make sense
        $radius = abs(round($radius));
        if ($radius == 0) {
            return $image;
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $imgCanvas = imagecreatetruecolor($width, $height);
        $imgBlur = imagecreatetruecolor($width, $height);

        // Gaussian blur matrix
        $matrix = [
            [1, 2, 1],
            [2, 4, 2],
            [1, 2, 1],
        ];
        imagecopy($imgBlur, $image, 0, 0, 0, 0, $width, $height);
        imageconvolution($imgBlur, $matrix, 16, 0);

        if ($threshold > 0) {
            $this->calcDifferenceBlurredThreshold($image, $imgBlur, $width, $height, $amount, $threshold);
        } else {
            $this->calcDifferenceBlurred($image, $imgBlur, $width, $height, $amount);
        }

        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);

        return $image;
    }

    /**
     * Calculate the difference between the blurred pixels and the original
     * and set the pixels.
     *
     * @param resource $image
     * @param resource $imageBlur
     * @param int $width
     * @param int $height
     * @param float $amount
     * @param int $threshold
     *
     * @return void
     */
    private function calcDifferenceBlurredThreshold(&$image, &$imageBlur, int $width, int $height, float $amount, int $threshold): void
    {
        for ($x = 0; $x < $width - 1; $x++) {
            // each row
            for ($y = 0; $y < $height; $y++) {
                // each pixel
                $rgbOrig = imagecolorat($image, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = imagecolorat($imageBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                // When the masked pixels differ less from the original
                // than the threshold specifies, they are set to their original value.
                $rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
                $gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
                $bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;

                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                    $pixCol = imagecolorallocate($image, $rNew, $gNew, $bNew);
                    imagesetpixel($image, $x, $y, $pixCol);
                }
            }
        }
    }

    /**
     * Calculate the difference between the blurred pixels and the original
     * and set the pixels.
     *
     * @param resource $img
     * @param resource $imgBlur
     * @param int $width
     * @param int $height
     * @param float $amount
     *
     * @return void
     */
    private function calcDifferenceBlurred(&$img, &$imgBlur, int $width, int $height, float $amount): void
    {
        for ($x = 0; $x < $width; $x++) {
            // each row
            for ($y = 0; $y < $height; $y++) {
                // each pixel
                $rgbOrig = imagecolorat($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = imagecolorat($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                if ($rNew > 255) {
                    $rNew = 255;
                } elseif ($rNew < 0) {
                    $rNew = 0;
                }
                $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                if ($gNew > 255) {
                    $gNew = 255;
                } elseif ($gNew < 0) {
                    $gNew = 0;
                }
                $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                if ($bNew > 255) {
                    $bNew = 255;
                } elseif ($bNew < 0) {
                    $bNew = 0;
                }
                $rgbNew = ($rNew << 16) + ($gNew << 8) + $bNew;
                imagesetpixel($img, $x, $y, $rgbNew);
            }
        }
    }

    /**
     * Destroy image resource.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->image !== null && is_resource($this->image)) {
            @imagedestroy($this->image);
        }
    }
}
