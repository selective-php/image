<?php

namespace Selective\Image\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Selective\Image\Image;

/**
 * ExampleTest.
 */
class ImageTest extends TestCase
{
    /**
     * @var Image|null
     */
    protected $image;

    /**
     * Image resource|null.
     */
    protected $imgSrc;

    /**
     * Set up this test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->image = new Image();
        $this->imgSrc = $this->image->getImage(__DIR__ . '/odan.jpg');
    }

    /**
     * Tear down this test.
     *
     * @return void
     */
    protected function tearDown()
    {
        @unlink(__DIR__ . '/odan.png');
        @unlink(__DIR__ . '/odan.gif');
        @unlink(__DIR__ . '/odan.bmp');
        @unlink(__DIR__ . '/new_odan.png');
        @unlink(__DIR__ . '/new_odan.jpg');
        $this->image = null;
        $this->imgSrc = null;
    }

    /**
     * Test create object.
     *
     * @return void
     */
    public function testImageClassInstance()
    {
        $this->assertInstanceOf(Image::class, $this->image);
    }

    public function testConvertImageShouldReturnTrue()
    {
        $this::assertTrue($this->image->convertImage($this->imgSrc, __DIR__ . '/odan.png', 0));
        $this::assertTrue($this->image->convertImage($this->imgSrc, __DIR__ . '/odan.gif'));
        $this::assertTrue($this->image->convertImage($this->imgSrc, __DIR__ . '/new_odan.jpg', 8));
        $this::assertTrue($this->image->convertImage($this->imgSrc, __DIR__ . '/odan.bmp'));
    }

    public function testConvertImageToBmp24()
    {
        $this->assertInternalType('string', $this->image->convertImageToBmp24($this->imgSrc));
    }

    public function testConvertImageToBmp16()
    {
        $this->assertInternalType('string', $this->image->convertImageToBmp16($this->imgSrc));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConvertImageToBmp24WithInvalidImgResource()
    {
        $resource = fopen('data://text/plain,invalid_img_resource', 'r');
        $this->image->convertImageToBmp24($resource);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConvertImageToBmp16WithInvalidImgResource()
    {
        $resource = fopen('data://text/plain,invalid_img_resource', 'r');
        $this->image->convertImageToBmp16($resource);
    }

    public function testGetImageData()
    {
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.png', 0);
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.bmp');
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.gif');

        $this->assertInternalType('string', $this->image->getImageData($this->imgSrc, 'jpg'));
        $this->assertInternalType('string', $this->image->getImageData($this->image->getImage(__DIR__ . '/odan.jpg'), 'jpg'));
        $this->assertInternalType('string', $this->image->getImageData($this->image->getImage(__DIR__ . '/odan.gif'), 'gif'));
        $this->assertInternalType('string', $this->image->getImageData($this->image->getImage(__DIR__ . '/odan.bmp'), 'bmp'));
    }

    public function testImageFromString()
    {
        $this->assertInternalType('resource', $this->image->imageFromString($this->image->getImageData($this->imgSrc, 'jpg')));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConvertFileWithError()
    {
        $this->image->convertFile(__DIR__ . '/../composer.json', __DIR__ . '/dest_file');
    }

    public function testConvertFile()
    {
        $this::assertTrue($this->image->convertFile(__DIR__ . '/odan.jpg', __DIR__ . '/new_odan.jpg', 0));
    }

    public function testAddWatermark()
    {
        $this->assertInternalType('resource', $this->image->addWatermark(__DIR__ . '/odan.jpg', __DIR__ . '/background.jpeg', ['sharpen' => true]));
    }

    public function testGetImage()
    {
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.png', 0);
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.gif');
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.bmp');

        $this->assertInternalType('resource', $this->image->getImage(__DIR__ . '/odan.png'));
        $this->assertInternalType('resource', $this->image->getImage(__DIR__ . '/odan.gif'));
        $this->assertInternalType('resource', $this->image->getImage(__DIR__ . '/odan.jpg'));
        $this->assertInternalType('resource', $this->image->getImage(__DIR__ . '/odan.bmp'));
    }

    public function testCreateImageFromBmp()
    {
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.bmp');

        $this->assertInternalType('resource', $this->image->createImageFromBmp(__DIR__ . '/odan.bmp'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateImageFromBmpWithInvalidFileType()
    {
        $this->image->createImageFromBmp(__DIR__ . '/odan.jpg');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCreateImageFromBmpWithInvalidImageFile()
    {
        $this->image->createImageFromBmp('invalid_image_file');
    }

    public function testResizeImage()
    {
        $this->assertInternalType('resource', $this->image->resizeImage($this->image->getImage(__DIR__ . '/odan.jpg'), 100));
    }

    public function testResizeFile()
    {
        $this::assertTrue($this->image->resizeFile(__DIR__ . '/odan.jpg', __DIR__ . '/new_odan.jpg', 100));
    }

    public function testDestroy()
    {
        $this::assertTrue($this->image->destroy($this->image->getImage(__DIR__ . '/odan.jpg')));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCopyImageResampledWithInvalidImageResource()
    {
        $resource = fopen('data://text/plain,invalid_img_resource', 'r');
        $this->image->copyImageResampled($resource, $resource, 0, 0, 0, 0, 0, 0, 0, 0, $quality = 3);
    }

    public function testConvertImageWithInvalidJpgQuality()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->image->convertImage($this->imgSrc, __DIR__ . '/odan.jpg', 101);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetImageDataWithInvalidResource()
    {
        $resource = fopen('data://text/plain,invalid_img_resource', 'r');
        $this->image->getImageData($resource, 'png');
    }

    public function testResizeFileWithInvalidResource()
    {
        $this->expectException(\Exception::class);
        $this->image->resizeFile('invalid_img', __DIR__ . '/new_odan.jpg', 100);
    }
}
