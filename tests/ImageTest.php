<?php

namespace Selective\Image\Test;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Selective\Image\Image;

/**
 * Test.
 */
class ImageTest extends TestCase
{
    /**
     * Set up this test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->getTestResultObject()->setTimeoutForSmallTests(600);
        $this->getTestResultObject()->setTimeoutForMediumTests(600);
        $this->getTestResultObject()->setTimeoutForLargeTests(600);
    }

    /**
     * Tear down this test.
     *
     * @return void
     */
    protected function tearDown()
    {
        @unlink(__DIR__ . '/new_example.png');
        @unlink(__DIR__ . '/new_example.jpg');
        @unlink(__DIR__ . '/new_example.gif');
        @unlink(__DIR__ . '/new_example.bmp');
    }

    /**
     * Test create object.
     *
     * @return void
     */
    public function testCreateFromFile()
    {
        Image::createFromFile(__DIR__ . '/example.png');
        Image::createFromFile(__DIR__ . '/example.gif');
        Image::createFromFile(__DIR__ . '/example.jpg');
        Image::createFromFile(__DIR__ . '/example.bmp');

        $this->assertTrue(true);
    }

    /**
     * Test create object.
     *
     * @return void
     */
    public function testCreateFromResource()
    {
        $handle = imagecreate(100, 100);
        if ($handle === false) {
            throw new RuntimeException(sprintf('Image could not be read'));
        }
        $this->assertIsResource($handle);
        Image::createFromResource($handle);

        $this->assertTrue(true);
    }

    public function testCreateFromResourceWithInvalidResource()
    {
        $this->expectException(\RuntimeException::class);

        $handle = fopen('data://text/plain,invalid_img_resource', 'r');
        if ($handle === false) {
            throw new RuntimeException(sprintf('File handle could not be created'));
        }

        Image::createFromResource($handle);
    }

    /**
     * @dataProvider saveProvider
     *
     * @param string $source
     * @param string $destination
     */
    public function testSave(string $source, string $destination)
    {
        Image::createFromFile($source)->save($destination);
        $this->assertFileExists($destination);
        Image::createFromFile($destination);
    }

    public function saveProvider(): array
    {
        $result = [];
        $extensions = ['png', 'gif', 'jpg', 'bmp'];

        foreach ($extensions as $extension) {
            foreach ($extensions as $extension2) {
                $result[] = [__DIR__ . '/example.' . $extension, __DIR__ . '/new_example.' . $extension2];
            }
        }

        return $result;
    }

    public function watermarkProvider(): array
    {
        $result = [];
        $extensions = ['png', 'gif', 'jpg', 'bmp'];

        foreach ($extensions as $extension) {
            foreach ($extensions as $extension2) {
                foreach ($extensions as $extension3) {
                    $result[] = [
                        __DIR__ . '/example.' . $extension,
                        __DIR__ . '/watermark.' . $extension2,
                        __DIR__ . '/new_example.' . $extension3, ];
                }
            }
        }

        return $result;
    }

    /**
     * @dataProvider watermarkProvider
     *
     * @param string $source
     * @param string $watermark
     * @param string $destination
     */
    public function testWatermark(string $source, string $watermark, string $destination)
    {
        Image::createFromFile($source)->watermark($watermark)->save($destination);
        $this->assertFileExists($destination);
        Image::createFromFile($destination);
    }

    /**
     * @dataProvider watermarkSharpenProvider
     *
     * @param string $source
     * @param string $watermark
     * @param string $destination
     */
    public function testWatermarkWithSharpen(string $source, string $watermark, string $destination)
    {
        Image::createFromFile($source)->watermark($watermark, ['sharpen' => true])->save($destination);
        $this->assertFileExists($destination);
        Image::createFromFile($destination);
    }

    public function watermarkSharpenProvider(): array
    {
        $result = [];
        $extensions = ['png', 'gif', 'jpg', 'bmp'];

        foreach ($extensions as $extension) {
            foreach ($extensions as $extension2) {
                $result[] = [
                    __DIR__ . '/example.' . $extension,
                    __DIR__ . '/watermark.' . $extension,
                    __DIR__ . '/new_example.' . $extension2, ];
            }
        }

        return $result;
    }

    /**
     * @dataProvider saveBmp16BitProvider
     *
     * @param string $source
     * @param string $destination
     */
    public function testSaveBmp16Bit(string $source, string $destination)
    {
        Image::createFromFile($source)->save($destination, 100, 16);
        $this->assertFileExists($destination);
    }

    public function saveBmp16BitProvider(): array
    {
        $result = [];
        $extensions = ['png', 'gif', 'jpg', 'bmp'];

        foreach ($extensions as $extension) {
            $result[] = [__DIR__ . '/example.' . $extension, __DIR__ . '/new_example.bmp'];
        }

        return $result;
    }

    public function testResize()
    {
        // todo
        $this->assertTrue(true);
    }
}
