<?php

namespace App\Service\Web;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploaderResult
{
    public function __construct(
        public string $newFilename,
        public string $originalName,
        public string $mimeType,
        public int    $size
    ) {}
}

class FileUploaderService
{
    public function __construct(
        private SluggerInterface $slugger
    ) {}

    public function upload(
        UploadedFile $file,
        string $absolutePath
    ): FileUploaderResult {
        // metadata před přesunem
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $mimeType         = $file->getClientMimeType();
        $size             = $file->getSize();

        // vytvoření “bezpečného” názvu + jedinečný sufix
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename  = sprintf('%s-%s.%s',
            $safeFilename,
            uniqid(),
            $file->guessExtension()
        );

        try {

            $file->move($absolutePath, $newFilename);

        } catch (FileException $e) {

            // tady případně logovat nebo přehodit vlastní výjimku
            throw $e;
            
        }

        return new FileUploaderResult(
            $newFilename,
            $originalFilename,
            $mimeType,
            $size
        );
    }
}
