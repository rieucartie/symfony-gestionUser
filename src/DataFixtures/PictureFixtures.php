<?php

namespace App\DataFixtures;

use App\Entity\Picture;
use App\Service\FileUploader;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureFixtures extends Fixture
{  
    private ObjectManager $manager;
    /**
     * @var array<string> 
     */
    private static array $pictures = [
        '2.jpeg',
        '3.jpeg',
        '4.jpeg', 
        '5.jpeg',
    ];  
    private string $filesToUploadDirectory;

    private string $uploadDirectory;

    private string $filesToUploadDirectoryCopy;

    private FileUploader $fileUploader;

    private Filesystem $fileSystem;

    public function __construct(FileUploader $fileUploader,kernelInterface $kernel,Filesystem $fileSystem,string $uploadDirectory){
        $this->fileSystem=$fileSystem;
         $this->fileUploader=$fileUploader;
        $this->filesToUploadDirectory="{$kernel->getProjectDir()}/public/to-upload/";
        $this->filesToUploadDirectoryCopy="{$kernel->getProjectDir()}/public/to-upload-copy/";
        $this->uploadDirectory=$uploadDirectory;
    }

    public function load(ObjectManager $manager):void
    {
       
        $this->manager=$manager;
        $this->copyToUploadDirectory();
        //$this->faker=Factory::create();
        $this->removeExistingUploadDirectoryAndRecreate();
        $this->generateArticlesPicures();
        $this->renameToUploadDirectoryCopy();
        $this->manager->flush();
    } 

private function renameToUploadDirectoryCopy():void
    {
       // $this->fileSystem->rename($this->filesToUploadDirectoryCopy,$this->filesToUploadDirectory);
     
    }

    private function removeExistingUploadDirectoryAndRecreate():void
    {
        if($this->fileSystem->exists($this->uploadDirectory)){
          $this->fileSystem->remove($this->uploadDirectory);
        $this->fileSystem->mkdir($this->uploadDirectory);  
        }
        
    }
 
    private function copyToUploadDirectory():void
    { 
        $this->fileSystem->mkdir($this->filesToUploadDirectoryCopy);
        $this->fileSystem->mirror($this->filesToUploadDirectory,$this->filesToUploadDirectoryCopy);

    }
    private function generateArticlesPicures():void
    { 
        foreach(self::$pictures as $key=>$pictureFile){
        $picture = new Picture();
       
            [
                'filename'=>$pictureName,
                'filePath'=>$picturePath
            ]=$this->fileUploader->upload(new UploadedFile( $this->filesToUploadDirectory.$pictureFile,$pictureFile,null,null,true));
        
            $picture->setPictureName($pictureName)
            ->setPicturePath($picturePath);
            $this->addReference("picture{$key}",$picture);
            $this->manager->persist($picture);
         
            if($key===array_key_last(self::$pictures)){
                //\rmdir($this->filesToUploadDirectory);
                $this->fileSystem->remove($this->uploadDirectory);
            }
        } 
    }

}
