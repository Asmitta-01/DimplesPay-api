<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getGroups(): array
    {
        return ['user'];
    }

    public function load(ObjectManager $manager): void
    {
        // Create first user
        $user1 = new User();
        $user1->setEmail('me@brayan.tiwa');

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user1,
            'Tiwa123'
        );
        $user1->setPassword($hashedPassword);

        $wallet1 = new Wallet();
        $wallet1->setUser($user1);
        $wallet1->setBalance(100000);

        // Create second user
        $user2 = new User();
        $user2->setEmail('user@dimples-pay.com');

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user2,
            'dimplesPay'
        );
        $user2->setPassword($hashedPassword);

        $wallet2 = new Wallet();
        $wallet2->setUser($user2);
        $wallet2->setBalance(100);

        $manager->persist($wallet1);
        $manager->persist($wallet2);

        $manager->persist($user1);
        $manager->persist($user2);


        // Flush to database
        $manager->flush();
    }
}
