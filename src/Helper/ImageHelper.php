<?php

declare(strict_types=1);

namespace App\Helper;

class ImageHelper
{
    private $uploadDir;
    private $height;
    private $width;

    public function __construct(string $uploadDir, int $height, int $width)
    {
        $this->uploadDir = $uploadDir;
        $this->height = $height;
        $this->width = $width;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);

            throw new \Exception('Upload directory does not exist: '.$this->uploadDir);
        }

        if (!is_writable($this->uploadDir)) {
            chmod($this->uploadDir, 0777);

            throw new \Exception('Upload directory is not writable: '.$this->uploadDir);
        }
    }

    public function uploadImage($file, $width, $height)
    {
        if (!$this->detectFileType($file['tmp_name'])) {
            return 'Error: Only .jpg and .png file types are allowed.';
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid().'.'.$extension;
        $destination = $this->uploadDir.$filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            if ('png' === $extension) {
                $convertedFile = $this->convertToJpg($destination);
                unlink($destination);
                $destination = $convertedFile;
            }
            $this->resizeImage($destination, $width, $height);

            return $filename;
        }

        return 'Error: Could not upload the file.';
    }

    private function detectFileType($file)
    {
        $mimeType = mime_content_type($file);

        return 'image/jpeg' === $mimeType || 'image/png' === $mimeType;
    }

    private function convertToJpg($file)
    {
        $image = imagecreatefrompng($file);
        $output = substr($file, 0, -3).'jpg';
        imagejpeg($image, $output, 100);
        imagedestroy($image);

        return $output;
    }

    private function resizeImage($file, $width, $height)
    {
        list($originalWidth, $originalHeight) = getimagesize($file);

        $resizedImage = imagecreatetruecolor($width, $height);
        $originalImage = imagecreatefromjpeg($file);

        imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);
        imagejpeg($resizedImage, $file, 100);

        imagedestroy($originalImage);
        imagedestroy($resizedImage);
    }
}
