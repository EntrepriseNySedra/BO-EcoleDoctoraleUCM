<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Absences;
use App\Entity\Etudiant;

class AbsencesFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        for($i=0; $i<= 10; $i++){
            $faker = \Faker\Factory::create('fr_FR');
            $absences = new Absences();
            $absences
                ->setUuid($faker->sentence(2, true))
                ->setJustification($faker->sentence(5, true))
                ->setDate((new \DateTime('now')))
                ->setCreatedAt((new \DateTime('now')))
            ;
            $manager->persist($absences);
        }

        $manager->flush();
    }
}
