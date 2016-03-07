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
 * @date 30/06/14.06.2014 18:04
 */

namespace Modules\Files\Tests\Cases;


use Mindy\Base\Mindy;
use StorageModel;
use Tests\DatabaseTestCase;

class ModelStorageTest extends DatabaseTestCase
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

        $this->initModels([new StorageModel()]);
    }

    public function tearDown()
    {
        $this->dropModels([new StorageModel()]);
        Mindy::setApplication(null);
        $this->app = null;
        parent::tearDown();
    }

    public function testInit()
    {
        $this->assertEquals(0, StorageModel::objects()->count());
    }

    public function testUpload()
    {
        $model = new StorageModel();
        /* @var $fileField \Mindy\Orm\Fields\FileField */
        $fileField = $model->getField('file');
        $this->assertInstanceOf('\Mindy\Orm\Fields\FileField', $fileField);

        // Set local file
        $fileField->setValue(__FILE__);
        // Файла нет
        $this->assertFalse($fileField->getPath());
        $this->assertNotNull($fileField->getValue());

        $model->setAttributes([
            'file' => __FILE__
        ]);
        $this->assertTrue($model->save());

        // magic __get method
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/ModelStorageTest.php', (string)$model->file);
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/ModelStorageTest.php', $model->file->getUrl());
        $this->assertEquals(
            realpath(__DIR__ . '/../www/public/models/StorageModel/' . date('Y-m-d') . '/ModelStorageTest.php'),
            $model->file->getPath()
        );
        $this->assertTrue(file_exists($model->file->getPath()));
        // $this->assertEquals(filesize(__FILE__), $model->file->getSize());

        $modelFresh = StorageModel::objects()->filter(['pk' => 1])->get();
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/ModelStorageTest.php', $modelFresh->file);
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/ModelStorageTest.php', (string)$modelFresh->file);
        $modelFresh->file = __DIR__ . '/StorageTest.php';
        $this->assertTrue($modelFresh->save());

        $modelTwo = new StorageModel();
        $modelTwo->file = __DIR__ . '/FilesTest.php';
        $this->assertTrue($modelTwo->save());
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/StorageTest.php', $modelFresh->getAttribute('file'));
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/StorageTest.php', $modelFresh->file->getUrl());
        $this->assertEquals('/public/models/StorageModel/' . date('Y-m-d') . '/FilesTest.php', $modelTwo->file->getUrl());
    }
}

