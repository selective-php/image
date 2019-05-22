# selective/image

Image manipulation library.

[![Latest Version on Packagist](https://img.shields.io/github/release/selective-php/image.svg)](https://packagist.org/packages/selective/image)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/selective-php/image.svg?branch=master)](https://travis-ci.org/selective-php/image)
[![Coverage Status](https://scrutinizer-ci.com/g/selective-php/image/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/selective-php/image/code-structure)
[![Quality Score](https://scrutinizer-ci.com/g/selective-php/image/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/selective-php/image/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/selective/image.svg)](https://packagist.org/packages/selective/image/stats)


## Features

* Converting images to JPEG, GIF, PNG, BMP (16-Bit and 24-Bit)
* Changing the size and sharpness of images

## Requirements

* PHP 7.2+
* The `GD` extension

## Recommended

* The `exif` extension, for better detection of the image format

## Installation

```
composer require selective/image
```

## Usage

### Open an image file

Create the image resource.

```php
use Selective\Image;

$image = Image::createFromFile('example.jpg');
```

### Open an image resource

```php
$resource = imagecreate(100, 100);
$image = Image::createFromResource($resource);
```

### Save image in desired format

Encodes the given image resource into given format/quality 
and save the the new image in filesystem.

```php
Image::createFromFile('example.jpg')->save('output.jpg');
```

Convert to JPG with 80% quality 

```php
Image::createFromFile('example.jpg')->save('output.jpg', 80);
```

Convert to PNG with 100% quality 

```php
Image::createFromFile('example.jpg')->save('output.png', 100);
```

Convert to BMP, 24-Bit colors

```php
Image::createFromFile('example.jpg')->save('output.bmp', 100, 24);
```

Convert to BMP, 16-Bit colors

```php
Image::createFromFile('example.jpg')->save('output.bmp', 100, 16);
```

### Resize image

Resize image to 800x600 pixel

```php
Image::createFromFile('example.jpg')->resize(800, 600)->save('output.jpg');
```

Resize image to 1024 pixel and dynamic height

```php
Image::createFromFile('example.jpg')->resize(1024)->save('output.jpg');
```

Resize image to 64x64 pixel and sharp the image

```php
Image::createFromFile('example.jpg')->resize(1024)->save('output.jpg', 64, 64, true);
```

### Insert watermark

```php
Image::createFromFile('example.jpg')->insert('watermark.png');
```

## Similar libraries

* https://github.com/Intervention/image

## License

* MIT