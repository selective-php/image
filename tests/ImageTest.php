<?php

namespace Odan\Image\Test;

use Odan\Image\Image;
use PHPUnit\Framework\TestCase;

/**
 * ExampleTest
 */
class ImageTest extends TestCase
{
    protected function tearDown()
    {
        @unlink('odan.jpg');
        @unlink('odan.gif');
        @unlink('odan.jpg');
    }

    /**
     * Test create object.
     *
     * @return void
     */
    public function testInstance()
    {
        $this->assertInstanceOf(Image::class, new Image());
    }

    public function testConvertImage()
    {

    }
}
