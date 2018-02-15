# Create Image

## Introduction

In this sample, you will learn how to create the image from image data string and file.

The image types that can support jpeg(jpg), png, gif and bmp now.

## Create image from image file

```php
$image = new Image();
$imgSrc = $image->getImage('/path/to/image.png');
```

## Create image from image data string

```php
$image = new Image();
$imgSrc = $image->imageFromString('image_data_string');
```

## Create image from bmp file

```php
$image = new Image();
$imgSrc = $image->createImageFromBmp('/path/to/image.bmp');
```

## Destorry image resource

After manipulating the image file, you should destroy image resource to free the memeory.
