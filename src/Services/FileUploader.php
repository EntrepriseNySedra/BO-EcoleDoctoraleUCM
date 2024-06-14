<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\ORM\EntityManagerInterface;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        ini_set('upload_max_filesize', '10M');
        $this->targetDirectory = $targetDirectory;
    }

    public function upload($file,$directory,$fileDirectory, $makeThumb = true)
    {
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // $newFilename = uniqid() . '.' . $file->guessExtension();
            $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($this->getTargetDirectory() . "/" . $fileDirectory, $newFilename);
                $targetDirectory = $this->getTargetDirectory() . "/" . $fileDirectory;
                if ($makeThumb) {
                    try {
                        //Bannière
                        $this->make_thumb($targetDirectory."/".$newFilename,$targetDirectory."/1920-".$newFilename,1920);
                        //Détails à la une
                        $this->make_thumb($targetDirectory."/".$newFilename,$targetDirectory."/1073-".$newFilename,1073);
                        //Miniature
                        $this->make_thumb($targetDirectory."/".$newFilename,$targetDirectory."/105-".$newFilename,105);
                    } catch (\Throwable $th) {
                    }
                }
            }
            catch (FileException $e) {
                //@todo Lancer un exeption ici
                die("Upload KO");
            }
        }
        
        return [
            "filename" => $newFilename,
        ];
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }   

    public function make_thumb($src, $dest, $desired_width) { 
        $exploded = explode('.',$src);
        $ext = $exploded[count($exploded) - 1]; 

        if (preg_match('/jpg|jpeg/i',$ext))
            $source_image=imagecreatefromjpeg($src);
        else if (preg_match('/png/i',$ext))
            $source_image=imagecreatefrompng($src);
        else if (preg_match('/gif/i',$ext))
            $source_image=imagecreatefromgif($src);
        else if (preg_match('/bmp/i',$ext))
            $source_image=imagecreatefrombmp($src);
        else
            return 0;

        /* read the source image */
        //$source_image = imagecreatefromjpeg($imageTmp);
        $width = imagesx($source_image);
        $height = imagesy($source_image);
        
        /* find the "desired height" of this thumbnail, relative to the desired width  */
        $desired_height = floor($height * ($desired_width / $width));
        
        /* create a new, "virtual" image */
        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
        
        /* copy source image at a resized size */
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
        
        /* create the physical thumbnail image to its destination */
        imagejpeg($virtual_image, $dest);
    }
}