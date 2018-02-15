# Resize Image

In this sample, you can learn how to let the source image be the new width and height.

## Resize image from image resource

The second parameter of the ```resizeImage``` method is the image quality.

That is, you can specify the image quality for the destination image when resizing image work.

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$newWidth = 100;
$newHeight = null;
$sharpen = true;
$imageDataString = $image->resizeImage($imgSrc, $newWidth, $newHeight, $sharpen);
```

## Resize image from image file

```php
$image = new Image();
$imageSrcFile = '/path/to/src_image.jpg';
$imageDestFile = '/path/to/resized_image.jpg';
$newWidth = 100;
$newHeight = null;
$sharpen = true;
$resultBoolean = $image->resizeFile($imageSrcFile, $imageDestFile, 100);
```

The default resized height is null, it's up to you for resizing the image height.

The default sharpen value is true.
