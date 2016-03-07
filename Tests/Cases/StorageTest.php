<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 25/06/14.06.2014 11:33
 */

namespace Modules\Files\Tests\Cases;


use Mindy\Base\Mindy;
use Mindy\Storage\FileSystemStorage;
use Mindy\Storage\MD5FileSystemStorage;
use Mindy\Storage\MimiBoxStorage;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app = Mindy::getInstance([
            'name' => 'Mindy',
            'basePath' => __DIR__ . '/../',
            'webPath' => __DIR__ . '/../www/',
            'components' => [
                'db' => [
                    'class' => '\Mindy\Orm\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'finder' => [
                    'class' => '\Mindy\Finder\FinderFactory',
                ],
                'storage' => [
                    'class' => '\Mindy\Storage\FileSystemStorage',
                    'baseUrl' => '/media/',
                ],
                'middleware' => [
                    'class' => '\Mindy\Middleware\MiddlewareManager',
                ],
                'viewRenderer' => [
                    'class' => '\Mindy\Renderer\Renderer',
                    'extensions' => [],
                    'globals' => [],
                    'functions' => [
                        'method_exists' => 'method_exists',
                        'get_menu' => '\Modules\Menu\Helpers\MenuHelper::renderMenu',
                        'get_block' => 'BlockHelper::render',
                        'debug_panel' => '\Modules\Core\Components\DebugPanel::render'
                    ],
                    'filters' => [],
                ],
            ]
        ]);
    }

    public function tearDown()
    {
        Mindy::setApplication(null);
        $this->app = null;

        $storage = new FileSystemStorage([
            'location' => __DIR__ . '/../media/',
            'baseUrl' => '/media/',
        ]);
        $storage->delete('test.txt');
        $storage->delete('test_1.txt');
        $storage->delete('test_2.txt');

        $storage->delete('098f6bcd4621d373cade4e832627b4f6.txt');
        $storage->delete('098f6bcd4621d373cade4e832627b4f6_1.txt');

        $storage->delete('test1.txt');

        parent::tearDown();
    }

    public function testStorage()
    {
        $storage = new FileSystemStorage();

        $this->assertEquals('/public/', $storage->baseUrl);
        $this->assertTrue(is_dir($storage->location));

        $this->assertTrue(file_exists($storage->path('.gitkeep')));
        $this->assertFalse($storage->path('foobar'));

        $this->assertEquals('/public/foobar', $storage->url('foobar'));
        $this->assertTrue($storage->exists('.gitkeep'));

        $this->assertNotNull($storage->accessedTime('.gitkeep'));
        $this->assertNotNull($storage->createdTime('.gitkeep'));
        $this->assertNotNull($storage->modifiedTime('.gitkeep'));

        $this->assertEquals('test.txt', $storage->save('test.txt', '123'));
        $this->assertEquals("123", $storage->open('test.txt'));

        $this->assertEquals('test_1.txt', $storage->save('test.txt', '123'));
        $this->assertEquals("123", $storage->open('test.txt'));

        $this->assertEquals(null, $storage->open('test1.txt'));
    }

    public function testMD5Storage()
    {
        $storage = new MD5FileSystemStorage();
        $name = $storage->save('test.txt', '123');
        $this->assertEquals('098f6bcd4621d373cade4e832627b4f6.txt', $name);
    }

    public function testMimiBoxStorage()
    {
        $storage = new MimiBoxStorage([
            'apiKey' => 'test',
            'username' => '123456',
        ]);
        $name = $storage->save('test1.txt', '123');
        $this->assertEquals('test1.txt', $name);
    }
}
