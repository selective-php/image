# Output Image

In this sample, you can learn how to output the image file and data string.

## Output image data string

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$imgDataString = $image->getImageData($imgSrc, 'jpg');
```

## Output image resource

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$imgDest = $image->getImage($imgSrc, 'jpg');
```
