# Add Watermark

In this sample, you will learn how to add the watermark to the specific image file.

## Add watermark

```php
$image = new Image();
$imageFile = '/path/to/source.jpg';
$imageWatermarkFile = '/path/to/background.jpeg';
$watermarkedImageResource = $image->addWatermark($imageFile, $imageWatermarkFile, ['sharpen' => true]);
```

The available parameters are as follows:

* ```sharpen``` parameter let image sharpen and the default value is false.
* ```w``` is the specific watermark image width and the default width is 1024px.
* ```h``` is the specific watermark image height and the default height is null.
* ```topPercent``` is to decide the watermark top position and the default value is 5.
* ```leftPercent``` is to decide the watermark left position and the default value is 5.
