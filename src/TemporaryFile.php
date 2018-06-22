<?php namespace SevenD;

use SplFileObject;

class TemporaryFile extends SplFileObject
{
    protected $temporaryFilePath;
    protected $temporaryDirectory;
    protected $persist = false;

    public function __construct($file_name, $open_mode = "r", $use_include_path = false, $context = null)
    {
        $temporaryDirectory = sprintf('%s/', rtrim(sys_get_temp_dir(), '/'));
        $temporaryFilePath = sprintf('%s%s', $temporaryDirectory, basename($file_name));

        copy($file_name, $temporaryFilePath);

        parent::__construct($temporaryFilePath, $open_mode, $use_include_path, $context);
        $this->setTemporaryFilePath($temporaryFilePath);
        $this->setTemporaryDirectory($temporaryDirectory);
    }

    public function __destruct()
    {
        if (file_exists($this->getTemporaryFilePath()) && !$this->shouldPersist()) {
            unlink($this->getTemporaryFilePath());
        } else {
            $path = $this->getTemporaryFilePath();
            register_shutdown_function(function() use($path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            });
        }
    }

    public static function createFromPath($path, $open_mode = "r", $use_include_path = false, $context = null)
    {
        return new TemporaryFile($path, $open_mode, $use_include_path, $context);
    }

    public static function createFromContents($contents, $extension, $filename = null, $open_mode = "r", $use_include_path = false, $context = null)
    {
        $path = sprintf('%s%s.%s', sprintf('%s/', rtrim(sys_get_temp_dir(), '/')), $filename ?: md5(time() . $contents), $extension);
        file_put_contents($path, $contents);

        $temporaryFile = new TemporaryFile($path, $open_mode, $use_include_path, $context);

        return $temporaryFile;
    }

    public static function createFromResource($resource, $extension, $filename = null, $open_mode = "r", $use_include_path = false, $context = null)
    {
        return self::createFromContents(stream_get_contents($resource), $extension, $filename, $open_mode, $use_include_path, $context);
    }

    public function createFromSplFileObject(SplFileObject $splFileObject, $open_mode = "r", $use_include_path = false, $context = null)
    {
        return self::createFromPath($splFileObject->getRealPath(), $open_mode, $use_include_path, $context);
    }

    public function getTemporaryFilePath()
    {
        return $this->temporaryFilePath;
    }

    public function setTemporaryFilePath($temporaryFilePath)
    {
        $this->temporaryFilePath = $temporaryFilePath;
        return $this;
    }

    public function getTemporaryDirectory()
    {
        return $this->temporaryDirectory;
    }

    public function setTemporaryDirectory($temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
        return $this;
    }

    public function shouldPersist(): bool
    {
        return $this->persist;
    }

    public function setPersist(bool $persist): TemporaryFile
    {
        $this->persist = $persist;
        return $this;
    }

    public function persist($persist = true)
    {
        $this->setPersist($persist);
        return $this;
    }
}
