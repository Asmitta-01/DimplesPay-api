<?php


namespace App\DataFixtures;

use App\Entity\GiftCard;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GiftCardFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $giftCard = new GiftCard();
            $giftCard->setAmount($i * 1000);
            $giftCard->setCode('GIFT' . str_pad($i, 6, '0', STR_PAD_LEFT));
            $manager->persist($giftCard);
        }

        $manager->flush();
    }
}
