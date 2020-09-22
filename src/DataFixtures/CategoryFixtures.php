<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;

class CategoryFixtures extends Fixture
{
    private ObjectManager $manager;
    
    public function load(ObjectManager $manager):void
    {
        $this->manager=$manager;
     
       
        $this->generateCategories(3);
        $this->manager->flush();
    }
    public function generateCategories(int $number):void{
      for($i=1;$i<=$number;$i++)
        {  
           
            $category=(new Category())->setName("category{$i}");
            $this->addReference("category{$i}",$category);
            $this->manager->persist($category);
        }
    }
    
}
