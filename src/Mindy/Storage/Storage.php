<?php

namespace Mindy\Storage;

use Mindy\Exception\Exception;
use Mindy\Helper\File;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

/**
 * Class Storage
 * @package Mindy\Storage
 */
abstract class Storage
{
    use Accessors, Configurator;

    /**
     * Retrieves the list of files and directories from storage py path
     * @param $path
     */
    abstract public function dir($path = null);

    /**
     * Retrieves the list of files and directories from storage py path
     * @param $path
     */
    abstract public function mkDir($path);

    /**
     * Retrieves the specified file from storage.
     * @param $name
     * @param string $mode
     */
    public function open($name, $mode = 'rb')
    {
        return $this->openInternal($name, $mode);
    }

    abstract protected function openInternal($name, $mode);

    public function getValidFileName($name)
    {
        return $name;
    }

    /**
     * Saves new content to the file specified by name. The content should be
     * a proper File object or any python file-like object, ready to be read
     * from the beginning.
     * @param $name
     * @param $content
     * @param $force bool Do not check available name - force rewrite.
     * @return mixed
     */
    public function save($name, $content, $force = false)
    {
        if (!$force) {
            $name = $this->getAvailableName($name);
        }
        return $this->saveInternal($name, $content) ? str_replace('\\', '/', $name) : false;
    }

    abstract protected function saveInternal($name, $content);

    /**
     * Returns a filename that's free on the target storage system, and
     * available for new content to be written to.
     * @param $name
     * @return string
     */
    public function getAvailableName($name)
    {
        $dirname = dirname($name);
        $ext = File::mbPathinfo($name, PATHINFO_EXTENSION);
        $fileName = File::mbPathinfo($name, PATHINFO_FILENAME);
        $fileName = $this->getValidFileName($fileName);

        $count = 0;
        $name = strtr("{dirname}/{filename}_{count}.{ext}", [
            '{dirname}' => $dirname,
            '{filename}' => $fileName,
            '{count}' => $count,
            '{ext}' => $ext
        ]);

        while ($this->exists($name)) {
            $count += 1;
            $name = strtr("{dirname}/{filename}_{count}.{ext}", [
                '{dirname}' => $dirname,
                '{filename}' => $fileName,
                '{count}' => $count,
                '{ext}' => $ext
            ]);
        }
        return $name;
    }

    abstract public function delete($name);

    /**
     * @param $name
     * @throws \Mindy\Exception\Exception
     * @return bool
     */
    abstract public function exists($name);

    /**
     * Retrieves the url address of file
     * @param $name
     */
    abstract public function url($name);

    /**
     * @param $name
     * @throws \Mindy\Exception\Exception
     * @return string
     */
    public function path($name)
    {
        throw new Exception("This backend doesn't support this feature.");
    }

    /**
     * @param $name
     * @return string
     */
    public function extension($name)
    {
        return File::mbPathinfo($name, PATHINFO_EXTENSION);
    }
}
