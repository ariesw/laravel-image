<?php

use Folklore\Image\Sources\FilesystemSource;
use Folklore\Image\ImagineManager;

/**
 * @coversDefaultClass Folklore\Image\Sources\FilesystemSource
 */
class SourcesFilesystemSourceTest extends TestCase
{
    protected $source;

    public function setUp()
    {
        parent::setUp();

        $config = $this->app['config']->get('image.sources.filesystem');
        $this->source = new FilesystemSource(app('image.imagine'), app('image.url'), $config);
    }

    public function tearDown()
    {
        if (file_exists(public_path('image-test.jpg'))) {
            unlink(public_path('image-test.jpg'));
        }

        if (file_exists(public_path('image-image(300x300).jpg'))) {
            unlink(public_path('image-image(300x300).jpg'));
        }

        if (file_exists(public_path('filesystem/image-test.jpg'))) {
            unlink(public_path('filesystem/image-test.jpg'));
        }

        if (file_exists(public_path('filesystem/image-image(300x300).jpg'))) {
            unlink(public_path('filesystem/image-image(300x300).jpg'));
        }

        parent::tearDown();
    }

    /**
     * Test that path exists
     * @test
     * @covers ::pathExists
     */
    public function testPathExists()
    {
        $this->assertTrue($this->source->pathExists('image.jpg'));
        $this->assertFalse($this->source->pathExists('not-found.jpg'));
    }

    /**
     * Test getting format from path
     * @test
     * @covers ::getFormatFromPath
     */
    public function testGetFormatFromPath()
    {
        $this->assertEquals('jpeg', $this->source->getFormatFromPath('image.jpg'));
        $this->assertEquals('png', $this->source->getFormatFromPath('image.png'));
    }

    /**
     * Test opening a path
     * @test
     * @covers ::openFromPath
     */
    public function testOpenFromPath()
    {
        $originalImage = app('image.imagine')->open(public_path('filesystem/image.jpg'));
        $image = $this->source->openFromPath('image.jpg');
        $this->assertEquals($originalImage->get('png'), $image->get('png'));
    }

    /**
     * Test gettingFilesFromPath
     * @test
     * @covers ::getFilesFromPath
     */
    public function testGetFilesFromPath()
    {
        if (!file_exists(public_path('filesystem/image-image(300x300).jpg'))) {
            copy(public_path('filesystem/image.jpg'), public_path('filesystem/image-image(300x300).jpg'));
        }

        $originalFiles = [
            '/image-image(300x300).jpg',
            '/image.jpg',
            '/image.png',
            '/wrong.jpg'
        ];
        $files = $this->source->getFilesFromPath('/');
        $this->assertEquals($originalFiles, $files);

        $originalFiles = [
            '/image-image(300x300).jpg',
            '/image.jpg'
        ];
        $files = $this->source->getFilesFromPath('image.jpg');
        $this->assertEquals($originalFiles, $files);

        $files = $this->source->getFilesFromPath('not-found');
        $this->assertEquals([], $files);
    }

    /**
     * Test saving to a path
     * @test
     * @covers ::saveToPath
     */
    public function testSaveToPath()
    {
        $originalImage = app('image.imagine')->open(public_path('filesystem/image.jpg'));
        $this->source->saveToPath($originalImage, 'image-test.jpg');
        $image = app('image.imagine')->open(public_path('filesystem/image-test.jpg'));
        $this->assertEquals($originalImage->getSize(), $image->getSize());
    }
}
