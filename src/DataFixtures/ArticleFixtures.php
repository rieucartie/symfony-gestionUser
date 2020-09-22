<?php

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;
use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    
    private \Faker\Generator $faker;
    private SluggerInterface $slugger;
    private ObjectManager $manager;
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger=$slugger;
    }
    public function load(ObjectManager $manager):void
    { 
        $this->manager=$manager;
        $this->faker=Factory::create();
        $this->generateArticles(3);
        $this->manager->flush();
    } 
    public function generateArticles(int $number):void
    { 
        for($i=0;$i<$number;$i++)
         {
        $article= new Article(); 
        [
            'dateObject' => $dateObject,
            'dateString' => $dateString
        ]=$this->generateRandomDateBeetweenRange('01/01/2020','11/09/2020');
        $picture=$this->getReference("picture{$i}");
        //$article->setTitle("Article {$i}")
        $title=$this->faker->sentence();
        $slug=$this->faker->slug(\strtolower($title)).'-'.$dateString;
        $article->setTitle($title)
        ->setContent($this->faker->paragraph())
        //->setSlug("article-{$i}-{$dateString}") 
        ->setSlug($slug)  
        ->setCreatedAt($dateObject)
        ->setIsPubished(false)
        // ->setAuthor($this->getReference("author" . mt_rand(0,1)))
        ->addCategory($this->getReference("category".mt_rand(1, 3)))
       ->setPicture($picture);
        $this->manager->persist($article);
        //comme c'est bidirectionel
            $picture->setArticle($article);

        //$this->manager->flush();
         }
    }
    /**
     * generate random date between
     * 
     * @param string  $start date
     * @param string $end date
     * @return array{dateObject:\dateTimeImmutable,dateString:string}
     */
    private function generateRandomDateBeetweenRange(string $start,string $end):array
    {
        //$startDateTimeStamp=(\DateTime::createFromFormat('d/m/Y',$start))->getTimestamp();
       // $endDateTimeStamp=(\DateTime::createFromFormat('d/m/Y',$end))->getTimestamp();
 
       // $randomTimeStamp=mt_rand($startDateTimeStamp,$endDateTimeStamp);
       // $dateTimeImmutable= (new \DateTimeImmutable())->setTimestamp($randomTimeStamp);
       $startDate=\DateTime::createFromFormat('d/m/Y',$start);
       $endDate=\DateTime::createFromFormat('d/m/Y',$end);
       if (!$startDate  || !$endDate)
       {
           throw new HttpException(400,"mauvais format de date 'd/m/Y'");
       }
       $randomTimeStamp=mt_rand($startDate->getTimestamp(),$endDate->getTimestamp());
       $dateTimeImmutable= (new \DateTimeImmutable())->setTimestamp($randomTimeStamp);
       return [
            'dateObject' => $dateTimeImmutable,
            'dateString' => $dateTimeImmutable->format('d-m-Y')   
        ];
    }  
    public function getDependencies(){
        return [
            PictureFixtures::class,
            CategoryFixtures::class
        ];
    }
}
