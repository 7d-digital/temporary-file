<?php

include('BaseTestCase.php');

class TemporaryFileTest extends BaseTestCase
{
    public function testTemporaryFileCreationFromConstructor()
    {
        $expectedFileContents = file_get_contents($this->getTestFilePath());

        $temporaryFile = new \SevenD\TemporaryFile($this->getTestFilePath());
        $temporaryFilePath = $temporaryFile->getRealPath();
	
        $actualFileContents = file_get_contents($temporaryFilePath);

        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
    }

    public function testTemporaryFileCreationFromDestructorUnset()
    {
        $expectedFileContents = file_get_contents($this->getTestFilePath());
        $temporaryFile = new \SevenD\TemporaryFile($this->getTestFilePath());

        $temporaryFilePath = $temporaryFile->getRealPath();
        unset($temporaryFile);

        $this->assertTrue(!file_exists($temporaryFilePath));
    }

    public function testTemporaryFileCreationFromDestructorOverwrite()
    {
        $temporaryFile = new \SevenD\TemporaryFile($this->getTestFilePath());
        
        $temporaryFilePath = $temporaryFile->getRealPath();
        $temporaryFile = false;

        $this->assertTrue(!file_exists($temporaryFilePath));
    }

    public function testTemporaryFileCreationFromDestructorGarbageCollector()
    {
        $temporaryFilePath = '';
        $closure = function() use (&$temporaryFilePath) {
            $temporaryFile = new \SevenD\TemporaryFile($this->getTestFilePath());
            $temporaryFilePath = $temporaryFile->getRealPath();
        };

        $closure();

        $this->assertTrue(strlen($temporaryFilePath) > 0);
        $this->assertTrue(!file_exists($temporaryFilePath));
    }

    public function testTemporaryFileCreationFromStaticConstructorPath()
    {
        $expectedFileContents = file_get_contents($this->getTestFilePath());

        $temporaryFile = \SevenD\TemporaryFile::createFromPath($this->getTestFilePath());
        $temporaryFilePath = $temporaryFile->getRealPath();

        $actualFileContents = file_get_contents($temporaryFilePath);
    
        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
    }

    public function testTemporaryFileCreationFromStaticConstructorContents()
    {
        $expectedFileContents = 'This is unique content';
        $extension = 'txt';
        $fileName = 'UniqueFileName';
        
        $temporaryFile = \SevenD\TemporaryFile::createFromContents($expectedFileContents, $extension, $fileName);
        $temporaryFilePath = $temporaryFile->getRealPath();

        $actualFileContents = file_get_contents($temporaryFilePath);
    
        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
        $this->assertEquals($extension, $temporaryFile->getExtension());
        $this->assertEquals($fileName, $temporaryFile->getBasename('.' . $extension));
    }

    public function testTemporaryFileCreationFromStaticConstructorContentsWithoutFileName()
    {
        $expectedFileContents = 'This is unique content';
        $extension = 'txt';

        $temporaryFile = \SevenD\TemporaryFile::createFromContents($expectedFileContents, $extension);
        $temporaryFilePath = $temporaryFile->getRealPath();

        $actualFileContents = file_get_contents($temporaryFilePath);

        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
        $this->assertEquals($extension, $temporaryFile->getExtension());
    }

    public function testTemporaryFileCreationFromStaticConstructorResource()
    {
        $expectedFileContents = file_get_contents($this->getTestFilePath());
        $extension = 'txt';
        $fileName = 'UniqueFileName';

        $handle = fopen($this->getTestFilePath(), 'r+');
        $temporaryFile = \SevenD\TemporaryFile::createFromResource($handle, $extension, $fileName);
        $temporaryFilePath = $temporaryFile->getRealPath();

        $actualFileContents = file_get_contents($temporaryFilePath);
    
        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
        $this->assertEquals($extension, $temporaryFile->getExtension());
        $this->assertEquals($fileName, $temporaryFile->getBasename('.' . $extension));
    }

    public function testTemporaryFileCreationFromStaticConstructorResourceWithoutFilename()
    {
        $expectedFileContents = file_get_contents($this->getTestFilePath());
        $extension = 'txt';

        $handle = fopen($this->getTestFilePath(), 'r+');
        $temporaryFile = \SevenD\TemporaryFile::createFromResource($handle, $extension);
        $temporaryFilePath = $temporaryFile->getRealPath();

        $actualFileContents = file_get_contents($temporaryFilePath);

        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
        $this->assertEquals($extension, $temporaryFile->getExtension());
    }

    public function testTemporaryFileCreationFromStaticConstructorSplFileObject()
    {
        $splFile = new \SplFileObject($this->getTestFilePath());
        $expectedFileContents = file_get_contents($this->getTestFilePath());

        $temporaryFile = \SevenD\TemporaryFile::createFromSplFileObject($splFile);
        $temporaryFilePath = $temporaryFile->getRealPath();

        $actualFileContents = file_get_contents($temporaryFilePath);
    
        $this->assertTrue(file_exists($temporaryFilePath));
        $this->assertEquals($expectedFileContents, $actualFileContents);
    }

    public function testPersistTypechange()
    {
        $temporaryFile = new \SevenD\TemporaryFile($this->getTestFilePath());

        $temporaryFile->persistUntil(\SevenD\TemporaryFile::PERSIST_UNTIL_SHUTDOWN);

        $this->assertTrue($temporaryFile->persistsUntil(\SevenD\TemporaryFile::PERSIST_UNTIL_SHUTDOWN));

        $temporaryFile->persistUntil(\SevenD\TemporaryFile::PERSIST_UNTIL_DESTRUCT);

        $this->assertTrue($temporaryFile->persistsUntil(\SevenD\TemporaryFile::PERSIST_UNTIL_DESTRUCT));
    }
}
