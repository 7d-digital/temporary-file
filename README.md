<p align="center">

<a href="https://7d-digital.co.uk"><img src="http://7d-digital.co.uk/images/structure/logo.svg" alt="Build Status"></a>

</p>

  

# temporary-file

  

Class for being able to instantiate temporary files for manipulation based on SplFileInfo

  

## Requirements

  

TemporaryFile makes use of the following:

  

- PHP 7.1

## Usage

Create a temporary file using path to existing file (file is copied)

    $temporaryFile = \SevenD\TemporaryFile::createFromPath('file.txt');

Create a temporary file using string content

    $temporaryFile = \SevenD\TemporaryFile::createFromContents('This is file contents', 'txt');

Create a temporary file using a file resource (file is copied)
	
    $fh = fopen('file.txt', 'r+');
    $temporaryFile = \SevenD\TemporaryFile::createFromResource($fh, 'txt');

Create a temporary file from a valid SplFileObject (file is copied)

    $splFileObject = new \SplFileObject('file.txt');
    $temporaryFile = \SevenD\TemporaryFile::createFromSplFileObject($splFileObject);

Set the persist mode for the temporary file

    $temporaryFile->persistUntil(\SevenD\TemporaryFile::PERSIST_UNTIL_DESTRUCT); // Temporary file is removed from the filesystem when the TemporaryFile object destructs [Default behaviour]
    $temporaryFile->persistUntil(\SevenD\TemporaryFile::PERSIST_UNTIL_SHUTDOWN); // Temporary file is removed from the filesystem when the script execution ends and the shutdown functions are run
