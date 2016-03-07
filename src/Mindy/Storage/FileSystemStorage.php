<?php

namespace Mindy\Storage;

use DirectoryIterator;
use FilesystemIterator;
use Mindy\Exception\Exception;
use Mindy\Helper\Alias;
use Mindy\Helper\Text;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class FileSystemStorage
 * @package Mindy\Storage
 */
class FileSystemStorage extends Storage
{
    /**
     * @var string
     */
    public $folderName = 'media';
    /**
     * @var string
     */
    public $location = '';
    /**
     * @var string
     */
    public $baseUrl = '/media/';

    public function init()
    {
        $this->location = Alias::get("www." . $this->folderName);
        if (!is_dir($this->location)) {
            throw new Exception("Directory not found.");
        }
        $this->location = realpath(rtrim($this->location, DIRECTORY_SEPARATOR));
    }

    public function size($name)
    {
        return filesize($this->path($name));
    }

    protected function openInternal($name, $mode)
    {
        if (!$this->exists($name)) {
            return null;
        }

        $path = $this->path($name);
        $handle = fopen($path, $mode, false);
        $contents = fread($handle, filesize($path));
        fclose($handle);
        return $contents;
    }

    public function path($name)
    {
        return realpath($this->location . DIRECTORY_SEPARATOR . $name);
    }

    protected function saveInternal($name, $content)
    {
        $directory = $this->location . DIRECTORY_SEPARATOR . dirname($name);
        if ($directory !== '.' && !is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        return file_put_contents($this->location . DIRECTORY_SEPARATOR . $name, $content) !== false;
    }

    public function delete($name)
    {
        $path = $this->path($name);
        if (is_file($path)) {
            return unlink($path);
        } else if (is_dir($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $iterPath) {
                if ($iterPath->isDir()) {
                    rmdir($iterPath->getPathname());
                } else {
                    unlink($iterPath->getPathname());
                }
            }
            return rmdir($path);
        }
        return false;
    }

    public function dir($path = null)
    {
        $path = $this->path($path);
        $folderStructure = [
            'directories' => [],
            'files' => []
        ];

        foreach (new DirectoryIterator($path) as $iteratedPath) {
            if (!$iteratedPath->isDot() && !Text::startsWith(basename($iteratedPath->getPathname()), '.')) {
                $key = $iteratedPath->isDir() ? 'directories' : 'files';
                $path = str_replace($this->location . DIRECTORY_SEPARATOR, '', $iteratedPath->getPathname());
                $folderStructure[$key][] = [
                    'path' => $path,
                    'url' => $this->url($path),
                    'name' => basename($path)
                ];
            }
        }

        return $folderStructure;
    }

    public function mkDir($path)
    {
        $path = $this->location . DIRECTORY_SEPARATOR . $path;
        return file_exists($path) ? false : mkdir($path);
    }

    public function url($name)
    {
        return $this->baseUrl . str_replace('\\', '/', $name);
    }

    /**
     * @param $name
     * @throws \Mindy\Exception\Exception
     * @return bool
     */
    public function exists($name)
    {
        return is_file($this->path($name));
    }

    /**
     * @param $name
     * @return int
     */
    public function accessedTime($name)
    {
        return fileatime($this->path($name));
    }

    /**
     * @param $name
     * @return int
     */
    public function createdTime($name)
    {
        return filectime($this->path($name));
    }

    /**
     * @param $name
     * @return int
     */
    public function modifiedTime($name)
    {
        return filemtime($this->path($name));
    }
}
