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

    public function testImageChecksum()
    {
        $this->assertSame('cf9bddd5a2621a2c1f825d900e025473c709951d', sha1_file(__DIR__ . '/example.png'));
        $this->assertSame('e2391b5e454d66829ec28998b745f4ca461e3d87', sha1_file(__DIR__ . '/example.jpg'));
        $this->assertSame('d8cab6095e8169778fb82ee3df22186f3d4dadf8', sha1_file(__DIR__ . '/example.gif'));
        $this->assertSame('90d9af8bed6a01008068b2a2ca46ca640fa692a7', sha1_file(__DIR__ . '/example.bmp'));
    }

    public function testImageCreateFromString()
    {
        // Test for PHP 7.1
        $data = 'iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABl'
            . 'BMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDr'
            . 'EX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r'
            . '8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==';
        $data = base64_decode($data);

        $this->assertIsResource(imagecreatefromstring($data) ?: false);
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
     * @large
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
     * @large
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
     * @large
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
     * @large
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
