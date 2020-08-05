<?php
/**
 * Auteur: Khaled Benharrat
 * Date: 19/06/2020
 */

namespace App\Service;

use App\Service\Slugify;
use Symfony\Component\HttpFoundation\File\UploadedFile;

//src/Service/FileUploader.php
class FileUploader
{
    private $targetDirectory;
    private $slugify;

    public function __construct($targetDirectory, Slugify $slugify)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugify = $slugify;
    }

    /**
     * Service Upload file (jpg, jpeg, png)
     * Finds the last available unique name
     * @param UploadedFile $file
     * @param string $fileName
     */
    public function upload(UploadedFile $file, ?string $fileName) :string
    {
        // initialize number file
        $fileNumber = 1;
        $safeFileName = $this->slugify->generate($fileName);
        $newFileName = $safeFileName . $fileNumber . '.' . $file->guessExtension();
        // Finds the last available unique name
        // verification unique filename in folder image
        while (file_exists($this->getTargetDirectory() . '/' . $newFileName)) {
            $fileNumber++;
            $newFileName = $safeFileName . $fileNumber . '.' . $file->guessExtension();
        }
        $file->move($this->getTargetDirectory(), $newFileName);

        return $newFileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
