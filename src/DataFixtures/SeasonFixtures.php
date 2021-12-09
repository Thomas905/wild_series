<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    const SEASONS = [
        '1',
        '2',
        '3',
        '4',
        '5',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SEASONS as $key => $seasonNumber) {  
            $season = new Season();
            $season->setNumber($seasonNumber);
            $season->setYear(2010);
            $season->setDescription("lorem ipsum vfreijg gfdkgt gifuejtr frhguirhgu jierufjerug fhreuifheriuf fjdgh kdfjsjd.");
            $manager->persist($season);
            $season->setProgram($this->getReference('program_0'));
            $this->addReference('season_' . $key, $season);
        }  
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
          ProgramFixtures::class,
        ];
    }
}
