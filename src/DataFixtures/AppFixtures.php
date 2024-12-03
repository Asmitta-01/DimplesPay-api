<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\Transaction;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
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
        $wallet1->setBalance(10000);

        $transaction1 = new Transaction();
        $transaction1->setAmount(10000)
            ->setDescription("Top-up wallet")
            ->setStatus('completed')
            ->setType('top-up')
            ->setUser($user1);

        $card1 = new Card();
        $card1->setSerialNumber('DIMPLES1234-9000P')
            ->setPinCode(12345)
            ->setCardNumber(13058490218)
            ->setUser($user1);

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
        $wallet2->setBalance(100000);

        $transaction2 = new Transaction();
        $transaction2->setAmount(100000)
            ->setDescription("Top-up wallet")
            ->setStatus('completed')
            ->setType('top-up')
            ->setUser($user2);

        $card2 = new Card();
        $card2->setSerialNumber('DIMPLES9204-4000P')
            ->setPinCode(02340)
            ->setCardNumber(19058410315)
            ->setUser($user2);

        // Persist
        $manager->persist($card1);
        $manager->persist($card2);

        $manager->persist($transaction1);
        $manager->persist($transaction2);

        $manager->persist($wallet1);
        $manager->persist($wallet2);

        $manager->persist($user1);
        $manager->persist($user2);

        // Flush to database
        $manager->flush();
    }
}
