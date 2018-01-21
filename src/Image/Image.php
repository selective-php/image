<?php

namespace Odan\Image;

use Exception;

/**
 * Image Class
 */
class Image
{
    /**
     * Save image object as file
     *
     * @param resource $image Image resource
     * @param string $filename Destination filename
     * @param integer $quality
     * @return bool
     */
    public function convertImage(&$image, $fileName, $quality = 100)
    {
        $result = false;
        $checkImageType = exif_imagetype($fileName);

        switch ($checkImageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($image, $filename, $quality);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($image, $filename);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($image, $filename, $quality);
                break;
            case IMAGETYPE_JPEG:
                $data = $this->convertImageToBmp24($image);
                $status = file_put_contents($filename, $data);
                $result = ($status !== false);
                break;
        }
        return $result;
    }

    /**
     * Convert image resource to bmp format (24 bit)
     *
     * @param resource $im
     * @return string $result binary data
     *
     * @link http://www.codingforums.com/archive/index.php/t-157438.html
     */
    public function convertImageToBmp24(&$im)
    {
        if (!is_resource($im)) {
            return false;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $result = '';

        if (!imageistruecolor($im)) {
            $tmp = imagecreatetruecolor($w, $h);
            imagecopy($tmp, $im, 0, 0, 0, 0, $w, $h);
            imagedestroy($im);
            $im = &$tmp;
        }

        $biBPLine = $w * 3;
        $biStride = ($biBPLine + 3) & ~3;
        $biSizeImage = $biStride * $h;
        $bfOffBits = 54;
        $bfSize = $bfOffBits + $biSizeImage;

        $result .= substr('BM', 0, 2);
        $result .= pack('VvvV', $bfSize, 0, 0, $bfOffBits);
        $result .= pack('VVVvvVVVVVV', 40, $w, $h, 1, 24, 0, $biSizeImage, 0, 0, 0, 0);

        $numpad = $biStride - $biBPLine;
        for ($y = $h - 1; $y >= 0; --$y) {
            for ($x = 0; $x < $w; ++$x) {
                $col = imagecolorat($im, $x, $y);
                $result .= substr(pack('V', $col), 0, 3);
            }
            for ($i = 0; $i < $numpad; ++$i) {
                $result .= pack('C', 0);
            }
        }
        return $result;
    }

    /**
     * Convert image resource to bmp format (16 bit)
     *
     * @param resource $im Image resource
     * @return string|bool $result binary data
     */
    public function convertImageToBmp16(&$im)
    {
        if (!is_resource($im)) {
            return false;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $result = '';

        if (!imageistruecolor($im)) {
            $tmp = imagecreatetruecolor($w, $h);
            imagecopy($tmp, $im, 0, 0, 0, 0, $w, $h);
            imagedestroy($im);
            $im = &$tmp;
        }

        $biBPLine = $w * 2;
        $biStride = ($biBPLine + 3) & ~3;
        $biSizeImage = $biStride * $h;
        $bfOffBits = 66;
        $bfSize = $bfOffBits + $biSizeImage;
        $result .= substr('BM', 0, 2);
        $result .= pack('VvvV', $bfSize, 0, 0, $bfOffBits);
        $result .= pack('VVVvvVVVVVV', 40, $w, '-' . $h, 1, 16, 3, $biSizeImage, 0, 0, 0, 0);
        $numpad = $biStride - $biBPLine;

        //for ($y = $h - 1; $y >= 0; --$y) {
        $result .= pack('VVV', 63488, 2016, 31);
        for ($y = 0; $y < $h; ++$y) {
            for ($x = 0; $x < $w; ++$x) {
                $rgb = imagecolorat($im, $x, $y);
                $r24 = ($rgb >> 16) & 0xFF;
                $g24 = ($rgb >> 8) & 0xFF;
                $b24 = $rgb & 0xFF;
                $col = ((($r24 >> 3) << 11) | (($g24 >> 2) << 5) | ($b24 >> 3));
                $result .= pack('v', $col);
            }
            for ($i = 0; $i < $numpad; ++$i) {
                $result .= pack('C', 0);
            }
        }
        return $result;
    }

    /**
     * Returns image binary data from image resource
     *
     * @param resource $im
     * @param string $type data type (jpg,png,gif,bmp)
     * @return string
     */
    public function getImageData($im, $type = 'jpg')
    {
        if (!is_resource($im)) {
            return false;
        }
        ob_start();
        if ($type == 'jpg') {
            imagejpeg($im);
        }
        if ($type == 'png') {
            imagepng($im);
        }
        if ($type == 'gif') {
            imagegif($im);
        }
        if ($type == 'bmp') {
            imagewbmp($im);
        }
        $result = ob_get_contents();
        ob_end_flush();
        return $result;
    }

    /**
     * Returns image from string
     *
     * @param array $data String containing the image data
     * @return resource
     */
    public function imageFromString($data)
    {
        return imagecreatefromstring($data);
    }

    /**
     * Converto image file to new format
     *
     * @param string $sourceFile
     * @param string $destFile
     * @param int $quality
     * @return bool
     */
    public function convertFile($sourceFile, $destFile, $quality = 100)
    {
        $im = $this->getImage($sourceFile);
        if (!is_resource($im)) {
            return false;
        }
        $result = $this->convertImage($im, $destFile, $quality);
        return $result;
    }

    /**
     * Add watermark to image
     *
     * @param string $backgroundFile background image filename
     * @param string $watermarkFile watermark image filename
     * @param array $params optional parameters
     * @return resource image
     */
    public function addWatermark($backgroundFile, $watermarkFile, array $params = array())
    {
        $w = gv($params, 'w', 1024);
        $h = gv($params, 'h', null);
        $sharpen = gv($params, 'sharpen', false);
        $topPercent = gv($params, 'top_percent', 5);
        $leftPercent = gv($params, 'left_percent', 5);

        $imgWatermark = $this->getImage($watermarkFile);
        $imgBackground = $this->getImage($backgroundFile);

        $imgBackground = $this->resizeImage($imgBackground, $w, $h, false);

        $srcW = imagesx($imgBackground);
        $srcH = imagesy($imgBackground);

        $srcPngW = imagesx($imgWatermark);
        $srcPngH = imagesy($imgWatermark);

        $dstW = $srcW;
        $dstH = $srcH;

        $dstPngW = $srcW / 3;
        $dstPngH = intval($srcPngH * $dstPngW / $srcPngW);

        $imgToLeft = ($dstW / 100) * $leftPercent;
        $imgToTop = ($dstH / 100) * $topPercent;

        $out = imagecreatetruecolor($dstW, $dstH);

        // 1. layer
        // $dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h
        imagecopyresampled($out, $imgBackground, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        // Append second layer (transparent png)
        imagecopyresampled($out, $imgWatermark, $imgToLeft, $imgToTop, 0, 0, $dstPngW, $dstPngH, $srcPngW, $srcPngH);

        if ($sharpen === true) {
            $amount = 50;
            $radius = 0.5;
            $threshold = 3;
            $out = $this->unsharpMask($out, $amount, $radius, $threshold);
        }
        return $out;
    }

    /**
     * Returns image resource by filename
     *
     * @param string $filename
     * @return resource
     */
    public function getImage($filename)
    {
        $im = false;
        $size = getimagesize($filename);
        switch ($size["mime"]) {
            case "image/jpeg":
                $im = imagecreatefromjpeg($filename);
                break;
            case "image/gif":
                $im = imagecreatefromgif($filename);
                break;
            case "image/png":
                $im = imagecreatefrompng($filename);
                break;
            case "image/bmp":
            case "image/x-ms-bmp":
                $im = $this->createImageFromBmp($filename);
                break;
        }
        return $im;
    }

    /**
     * Create image resource from bmp file
     *
     * @param string $filename
     * @return resource
     */
    public function createImageFromBmp($filename)
    {
        // open the file in binary mode
        if (!$f1 = fopen($filename, "rb")) {
            return false;
        }

        // load file header
        $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
        if ($FILE['file_type'] != 19778) {
            return false;
        }

        // load bmp headers
        $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
            '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
            '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
        $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
        if ($BMP['size_bitmap'] == 0) {
            $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
        }
        $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
        $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
        $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
        $BMP['decal'] = 4 - (4 * $BMP['decal']);
        if ($BMP['decal'] == 4) {
            $BMP['decal'] = 0;
        }

        // load color palette
        $PALETTE = array();
        if ($BMP['colors'] < 16777216) {
            $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
        }

        // create image
        $IMG = fread($f1, $BMP['size_bitmap']);
        $VIDE = chr(0);

        $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
        $P = 0;
        $Y = $BMP['height'] - 1;
        while ($Y >= 0) {
            $X = 0;
            while ($X < $BMP['width']) {
                if ($BMP['bits_per_pixel'] == 24) {
                    $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
                } elseif ($BMP['bits_per_pixel'] == 16) {
                    $COLOR = unpack("n", substr($IMG, $P, 2));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 8) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 4) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 2) % 2 == 0) {
                        $COLOR[1] = ($COLOR[1] >> 4);
                    } else {
                        $COLOR[1] = ($COLOR[1] & 0x0F);
                    }
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } elseif ($BMP['bits_per_pixel'] == 1) {
                    $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                    if (($P * 8) % 8 == 0) {
                        $COLOR[1] = $COLOR[1] >> 7;
                    } elseif (($P * 8) % 8 == 1) {
                        $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                    } elseif (($P * 8) % 8 == 2) {
                        $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                    } elseif (($P * 8) % 8 == 3) {
                        $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                    } elseif (($P * 8) % 8 == 4) {
                        $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                    } elseif (($P * 8) % 8 == 5) {
                        $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                    } elseif (($P * 8) % 8 == 6) {
                        $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                    } elseif (($P * 8) % 8 == 7) {
                        $COLOR[1] = ($COLOR[1] & 0x1);
                    }
                    $COLOR[1] = $PALETTE[$COLOR[1] + 1];
                } else {
                    return false;
                }
                imagesetpixel($res, $X, $Y, $COLOR[1]);
                $X++;
                $P += $BMP['bytes_per_pixel'];
            }
            $Y--;
            $P += $BMP['decal'];
        }

        // close the file
        fclose($f1);
        return $res;
    }

    /**
     * Resize image resource
     *
     * @param resource $image
     * @param int $width
     * @param int $height
     * @param bool $sharpen
     * @return resource
     */
    public function resizeImage($image, $width, $height = null, $sharpen = true)
    {
        $widthOrig = imagesx($image);
        $heightOrig = imagesy($image);

        if ($width > $widthOrig) {
            $height = $heightOrig;
            $width = $widthOrig;
        }

        if ($height === null) {
            // proportional resize
            $height = intval($heightOrig * $width / $widthOrig);
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
     * Resize image
     *
     * @param string $sourceFile
     * @param string $destFile
     * @param int $width
     * @param int $height
     * @param bool $sharpen
     * @return bool
     */
    public function resizeFile($sourceFile, $destFile, $width, $height = null, $sharpen = true)
    {
        $image = $this->getImage($sourceFile);
        if ($image === false) {
            throw new Exception('Invalid image format');
        }
        $image2 = $this->resizeImage($image, $width, $height, $sharpen);
        // save to file
        $result = imagejpeg($image2, $destFile, 100);
        return $result;
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
     * @param int $quality
     * @return bool
     */
    public function copyImageResampled(&$dstImage, $srcImage, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH, $quality = 3)
    {
        if (empty($srcImage) || empty($dstImage) || $quality <= 0) {
            return false;
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
     * @param resource $img
     * @param float $amount
     * @param float $radius
     * @param int $threshold
     * @return resource
     */
    protected function unsharpMask($img, $amount, $radius, $threshold)
    {
        // $img is an image that is already created within php using
        // imgcreatetruecolor. No url! $img must be a truecolor image.
        // Attempt to calibrate the parameters to Photoshop:
        if ($amount > 500) {
            $amount = 500;
        }
        $amount = $amount * 0.016;
        if ($radius > 50) {
            $radius = 50;
        }
        $radius = $radius * 2;
        if ($threshold > 255) {
            $threshold = 255;
        }
        // Only integers make sense
        $radius = abs(round($radius));
        if ($radius == 0) {
            return $img;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);

        // Gaussian blur matrix
        $matrix = array(
            array(1, 2, 1),
            array(2, 4, 2),
            array(1, 2, 1)
        );
        imagecopy($imgBlur, $img, 0, 0, 0, 0, $w, $h);
        imageconvolution($imgBlur, $matrix, 16, 0);

        if ($threshold > 0) {
            $this->calcDifferenceBlurredThreshold($img, $imgBlur, $w, $h, $amount, $threshold);
        } else {
            $this->calcDifferenceBlurred($img, $imgBlur, $w, $h, $amount);
        }

        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);
        return $img;
    }

    /**
     * Calculate the difference between the blurred pixels and the original
     * and set the pixels
     *
     * @param resource $img
     * @param resource $imgBlur
     * @param int $w
     * @param int $h
     * @param int $amount
     * @param int $threshold
     * @return void
     */
    protected function calcDifferenceBlurredThreshold(&$img, &$imgBlur, $w, $h, $amount, $threshold)
    {
        for ($x = 0; $x < $w - 1; $x++) {
            // each row
            for ($y = 0; $y < $h; $y++) {
                // each pixel
                $rgbOrig = imagecolorat($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = imagecolorat($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                // When the masked pixels differ less from the original
                // than the threshold specifies, they are set to their original value.
                $rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) : $rOrig;
                $gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) : $gOrig;
                $bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) : $bOrig;

                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                    $pixCol = imagecolorallocate($img, $rNew, $gNew, $bNew);
                    imagesetpixel($img, $x, $y, $pixCol);
                }
            }
        }
    }

    /**
     * Calculate the difference between the blurred pixels and the original
     * and set the pixels
     *
     * @param resource $img
     * @param resource $imgBlur
     * @param int $w
     * @param int $h
     * @param int $amount
     * @return void
     */
    protected function calcDifferenceBlurred(&$img, &$imgBlur, $w, $h, $amount)
    {
        for ($x = 0; $x < $w; $x++) {
            // each row
            for ($y = 0; $y < $h; $y++) {
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
     * Detroy image resource
     *
     * @param resource $im image
     * @return array
     */
    public function destroy($im)
    {
        imagedestroy($im);
    }
}
