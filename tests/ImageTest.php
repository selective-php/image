<?php

namespace Odan\Image\Test;

use Odan\Image\Image;
use PHPUnit\Framework\TestCase;

/**
 * ExampleTest
 */
class ImageTest extends TestCase
{
    /**
     * Test create object.
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Image::class, new Image());
    }
}
