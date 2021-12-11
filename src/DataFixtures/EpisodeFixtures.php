<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    const EPISODES = [
        '1',
        '2',
        '3',
        '4',
        '5',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::EPISODES as $key => $episodeNumber) {  
            $episode = new Episode();
            $episode->setTitle($episodeNumber);
            $episode->setNumber(2010);
            $episode->setSynopsis("Episode description");
            $manager->persist($episode);
            $episode->setSeason($this->getReference('season_0'));
            $episode->setSlug($this->slugify->generate($episode->setTitle($episodeNumber)));
            $this->addReference('episode_' . $key, $episode);
        }  
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
          SeasonFixtures::class,
        ];
    }
}
