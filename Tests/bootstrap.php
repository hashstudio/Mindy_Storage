<?php

require_once(__DIR__ . '/../../../autoload.php');

require_once(__DIR__ . '/../src/Mindy/Storage/Storage.php');
require_once(__DIR__ . '/../src/Mindy/Storage/FileSystemStorage.php');
require_once(__DIR__ . '/../src/Mindy/Storage/MD5FileSystemStorage.php');
require_once(__DIR__ . '/../src/Mindy/Storage/MimiBoxStorage.php');
require_once(__DIR__ . '/../src/Mindy/Storage/Files/File.php');
require_once(__DIR__ . '/../src/Mindy/Storage/Files/UploadedFile.php');
require_once(__DIR__ . '/../src/Mindy/Storage/Files/LocalFile.php');

require_once(__DIR__ . '/../../orm/Tests/TestCase.php');
require_once(__DIR__ . '/../../orm/Tests/DatabaseTestCase.php');

require_once(__DIR__ . '/models.php');
