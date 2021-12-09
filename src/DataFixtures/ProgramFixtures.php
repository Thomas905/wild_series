<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROGRAM = [
        'La belle au bois dormant',
        'Jaquie et michel',
        'Le film de noel',
        'Incrotable talent de développeur',
        'Ylaris mange du chocolat au pomme',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::PROGRAM as $key => $programName) {
            $program = new Program();
            $program->setTitle($programName);
            $program->setSynopsis('Une super série');
            $program->setYear('2010');
            $program->setPoster('https://fr.web.img6.acsta.net/c_210_280/pictures/210/454/21045474_20130930201634487.jpg');
            $program->setCountry('USA');
            $program->setCategory($this->getReference('category_0'));
            for ($i=0; $i < count(ActorFixtures::ACTORS); $i++) {
                $program->addActor($this->getReference('actor_' . $i));
            }
            $this->addReference('program_' . $key, $program);
            $manager->persist($program);
        }
        
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
          ActorFixtures::class,
          CategoryFixtures::class,
        ];
    }


}