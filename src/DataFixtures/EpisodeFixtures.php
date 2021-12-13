<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    const EPISODES = [
        '1',
        '2',
        '3',
        '4',
        '5',
    ];

    private Slugify $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::EPISODES as $key => $episodeNumber) {  
            $episode = new Episode();
            $episode->setNumber($episodeNumber);
            $episode->setTitle('Un super episode');
            $episode->setSlug($this->slugify->generate($episode->getTitle()));
            $episode->setSynopsis("lorem ipsum vfreijg.");
            $manager->persist($episode);
            $episode->setSeason($this->getReference('season_0'));
        }  
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
          SeasonFixtures::class,
        ];
    }
}