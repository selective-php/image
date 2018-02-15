# odan/image

Image manipulation library.

[![Latest Version on Packagist](https://img.shields.io/github/release/odan/image.svg)](https://github.com/odan/image/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/odan/image.svg?branch=master)](https://travis-ci.org/odan/image)
[![Code Coverage](https://scrutinizer-ci.com/g/odan/image/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/odan/image/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/odan/image/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/odan/image/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/odan/image.svg)](https://packagist.org/packages/odan/image)


## Features

* Converting images to JPEG, GIF, PNG, BMP (16-Bit and 24-Bit)
* Changing the size and sharpness of images

## Requirements

* The ```GD``` extension shoud be required.

## Installation

```
composer require odan/image
```

## Usage

Create the image resource.

```php
$image = new Image();

$imgSrc = $image->getImage('/path/to/odan.jpg');
```

Convert the image to the specific image type and get converted image data string.

```php
$image->convertImage($imgSrc, '/path/to/odan.png', 0);
$imageData = $image->getImageData($imgSrc, 'png'));
```

Convert the image to the specific image type and get converted image data string.
```php
$image->convertImage($imgSrc, '/path/to/odan.png', 0);
$imageResource = $image->getImage('/path/to/odan.png');
```

If you want more details and examples about usage, please see the ```README.md``` file in ```docs``` folder.
