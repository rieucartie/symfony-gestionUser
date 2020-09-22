<?php
namespace App\Service;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class FileUploader{
private SluggerInterface $slugger;
private string $uploadDirectory;

public function __construct(SluggerInterface $slugger,  string $uploadDirectory)
    {
        $this->slugger=$slugger;
        $this->uploadDirectory=$uploadDirectory;
    } 
    /**  
     * @param UploadedFile $file
     * @return array<string>
     */
    public function upload(UploadedFile $file):array
    {
        $filename=$this->generateFileUniqueName($file);
        try{
            $file->move( $this->uploadDirectory,$filename);  
        }
        catch(fileException $fileException){
            throw $fileException;
        }
        return
        [
           'filename' =>$filename,
           'filePath' =>$this->uploadDirectory.$filename,
        ];
    }
    private function generateFileUniqueName(UploadedFile $file):string
    {
        $originalfilename=pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
        $originalfilenameSlugged=$this->slugger->slug(strtolower($originalfilename));
        $randomID=\uniqid();
        return "{$originalfilenameSlugged}-{$randomID}.{$file->getExtension()}";

    }
}