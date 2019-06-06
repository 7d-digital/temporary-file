<?php namespace SevenD;

use SplFileObject;
use InvalidArgumentException;

class TemporaryFile extends SplFileObject
{
    const PERSIST_UNTIL_DESTRUCT = 1;
    const PERSIST_UNTIL_SHUTDOWN = 2;

    protected $persistTypes = [
        self::PERSIST_UNTIL_DESTRUCT,
        self::PERSIST_UNTIL_SHUTDOWN
    ];

    protected $temporaryFilePath;
    protected $temporaryDirectory;
    protected $persist = self::PERSIST_UNTIL_DESTRUCT;

    /**
     * Create a Temporary file object from path
     *
     * @param string $path
     * @return void
     */
    public function __construct(string $path, $openMode = 'r', $useIncludePath = false, $context = null)
    {
        $temporaryDirectory = sprintf('%s/', rtrim(sys_get_temp_dir(), '/'));
        $temporaryFilePath = sprintf('%s%s', $temporaryDirectory, basename($path));

        copy($path, $temporaryFilePath);

        parent::__construct($temporaryFilePath, $openMode, $useIncludePath, $context);

        $this->setTemporaryFilePath($temporaryFilePath);
        $this->setTemporaryDirectory($temporaryDirectory);
    }

    /**
     * Remove (or set up removal of) the temporary file
     *
     * @param string $path
     * @return void
     */
    public function __destruct()
    {
        $path = $this->getTemporaryFilePath();

        // Check files exists, we don't need to do anything if it doesn't.
        if (file_exists($this->getTemporaryFilePath())) {
            if ($this->persistsUntil(self::PERSIST_UNTIL_DESTRUCT)) {
                // Remove file if persist type is until the objects destruction. This could be via;
                // 1) unset()
                // 2) Reassignment of variable orphaning the object
                // 3) Variable unset by Garbage Collector (variable becomes unreachable)
                unlink($path);
            } elseif ($this->persistsUntil(self::PERSIST_UNTIL_SHUTDOWN)) {
                // Register shutdown event to remove file if persist type is until the script shutdown
                register_shutdown_function(function() use($path) {
                    // Must check file existance again as time this is psuedo-asynchronous
                    if (file_exists($path)) {
                        unlink($path);
                    }
                });
            }
        }
    }

    /**
     * Static constructor for creating temporary file object from a path
     *
     * @param string $path
     * @return \SevenD\TemporaryFile
     */
    public static function createFromPath($path, $openMode = 'r', $useIncludePath = false, $context = null)
    {
        return new TemporaryFile($path, $openMode, $useIncludePath, $context);
    }

    /**
     * Static constructor for creating temporary file object from a raw contents
     *
     * @param string $contents
     * @param string $extension
     * @param string $filename
     * @return \SevenD\TemporaryFile
     */
    public static function createFromContents(string $contents, string $extension, string $filename = null, $openMode = 'r', $useIncludePath = false, $context = null)
    {
        $path = sprintf('%s%s.%s', sprintf('%s/', rtrim(sys_get_temp_dir(), '/')), $filename ?: md5(random_bytes(8) . microtime() . $contents), $extension);
       
        file_put_contents($path, $contents);

        $temporaryFile = new TemporaryFile($path, $openMode, $useIncludePath, $context);

        return $temporaryFile;
    }

    /**
     * Static constructor for creating temporary file object from a file resource
     *
     * @param resource $resource
     * @param string $extension
     * @param string $filename
     * @return \SevenD\TemporaryFile
     */
    public static function createFromResource($resource, string $extension, string $filename = null, $openMode = 'r', $useIncludePath = false, $context = null)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Argument 1 of createFromResource should be resource.');
        }
        return self::createFromContents(stream_get_contents($resource), $extension, $filename, $openMode, $useIncludePath, $context);
    }

    /**
     * Static constructor for creating temporary file object from a SplFileObject
     *
     * @param SplFileObject $splFileObject
     * @return \SevenD\TemporaryFile
     */
    public static function createFromSplFileObject(SplFileObject $splFileObject, $openMode = 'r', $useIncludePath = false, $context = null)
    {
        return self::createFromPath($splFileObject->getRealPath(), $openMode, $useIncludePath, $context);
    }

    /**
     * Get the temporary files path
     *
     * @return string
     */
    public function getTemporaryFilePath()
    {
        return $this->temporaryFilePath;
    }

    /**
     * Set the path to the temporary file
     *
     * @param string $temporaryFilePath
     * @return \SevenD\TemporaryFile
     */
    protected function setTemporaryFilePath($temporaryFilePath)
    {
        $this->temporaryFilePath = $temporaryFilePath;
        return $this;
    }

    /**
     * Get the path to the temporary file's directory
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->temporaryDirectory;
    }

    /**
     * Set the path to the temporary file's directory
     *
     * @param string $temporaryDirectory
     * @return \SevenD\TemporaryFile
     */
    protected function setTemporaryDirectory($temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
        return $this;
    }

    /**
     * Test the current persist type
     *
     * @param integer $persistType
     * @return boolean
     */
    public function persistsUntil(int $persistType): bool
    {
        if (!$this->isValidPersistType($persistType)) {
            throw new InvalidArgumentException('Unable to test persist type of TemporaryFile. Supported persist type supplied.');
        }
        return $this->persist == $persistType;
    }

    /**
     * Set the persist type
     *
     * @param integer $persistType
     * @return \SevenD\TemporaryFile
     */
    public function persistUntil(int $persistType): TemporaryFile
    {
        if (!$this->isValidPersistType($persistType)) {
            throw new InvalidArgumentException('Unable to set persist type of TemporaryFile. Supported persist type supplied.');
        }

        $this->persist = $persistType;
        return $this;
    }

    /**
     * Check the value is a valid persist type
     *
     * @param integer $persistType
     * @return boolean
     */
    protected function isValidPersistType($persistType): bool
    {
        return in_array($persistType, $this->persistTypes);        
    }
}
