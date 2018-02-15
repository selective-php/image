# Convert Image

In this sample, you will learn how to convert the image to the specific image type.

## Convert image to the BMP 14 image

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$imageDataString = $image->convertImageToBmp24($imgSrc);
```

## Convert image to the BMP 16 image

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$imageDataString = $image->convertImageToBmp16($imgSrc);
```

## Convert image to the BMP 24 image

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$resizedImageResource = $image->convertImageToBmp24($imgSrc);
```

## Convert image from image resource

This sample code will be automatic to output specific image file.

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
$resultBoolean = $image->convertImage($imgSrc, '/path/to/output.jpg');
```

## Convert image from image file

```php
$image = new Image();
$imgSrc = '/path/to/image.png';
$resultBoolean = $image->convertFile($imgFile, '/path/to/output.jpg');
```
