# selective/image

Image manipulation library.

[![Latest Version on Packagist](https://img.shields.io/github/release/selective-php/image.svg)](https://packagist.org/packages/selective/image)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/selective-php/image.svg?branch=master)](https://travis-ci.org/selective-php/image)
[![Coverage Status](https://scrutinizer-ci.com/g/selective-php/image/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/selective-php/image/code-structure)
[![Total Downloads](https://img.shields.io/packagist/dt/selective/image.svg)](https://packagist.org/packages/selective/image/stats)


## Features

* Convert images to JPEG, GIF, PNG, BMP (16-Bit and 24-Bit)
* Change the size and sharpness of images
* Insert watermark image

## Requirements

* PHP 7.2+
* The `GD` extension

## Installation

```
composer require selective/image
```

## Usage

### Open image file

Creation of an image instance:

```php
use Selective\Image;

$image = Image::createFromFile('example.jpg');
```

### Open image resource

```php
$resource = imagecreate(100, 100);
$image = Image::createFromResource($resource);
```

### Save image in desired format

Encodes the given image instance into a specific format/quality
and save the new image as a file.

```php
Image::createFromFile('example.jpg')->save('output.jpg');
```

Convert to JPG with 80% quality:

```php
Image::createFromFile('example.jpg')->save('output.jpg', 80);
```

Convert to PNG with 100% quality:

```php
Image::createFromFile('example.jpg')->save('output.png', 100);
```

Convert to BMP, 24-Bit colors:

```php
Image::createFromFile('example.jpg')->save('output.bmp', 100, 24);
```

Convert to BMP, 16-Bit colors:

```php
Image::createFromFile('example.jpg')->save('output.bmp', 100, 16);
```

### Resize image

Resize image to 800x600 pixel:

```php
Image::createFromFile('example.jpg')->resize(800, 600)->save('output.jpg');
```

Resize image to 1024 pixel and dynamic height:

```php
Image::createFromFile('example.jpg')->resize(1024)->save('output.jpg');
```

Resize image to 64x64 pixel and sharp the image:

```php
Image::createFromFile('example.jpg')->resize(1024)->save('output.jpg', 64, 64, true);
```

### Insert watermark

```php
Image::createFromFile('example.jpg')->insert('watermark.png')->save('output.jpg');
```

## Similar libraries

* https://github.com/Intervention/image
* https://github.com/spatie/image

## License

* MIT
