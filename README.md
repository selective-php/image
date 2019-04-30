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

* The `GD` extension

## Recommended

* The `exif` extension, for better detection of the image format

## Installation

```
composer require selective/image
```

## Usage

Create the image resource.

```php
$image = new \Selective\Image\Image();

$imageResource = $image->getImage('/path/to/file.jpg');
```

Encodes the given image resource into given format/quality 
and save the the new image in filesystem.

```php
$image->convertImage($imageResource, '/path/to/new-file.png', 100);
```

Get the content of the given image resource.

```php
$content = $image->getImageData($imageResource);
```

If you want more details and examples about the usage, please read the [documentation](docs/README.md).
