<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[AsController]
class WalletController extends AbstractController
{
    #[Route('/api/wallet/balance', methods: ['GET'])]
    public function getBalance(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return $this->json([
            'balance' => $currentUser->getWallet()->getBalance()
        ]);
    }

    #[Route('/api/wallet/topup', methods: ['POST'])]
    public function topUp(EntityManagerInterface $entityManagerInterface, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;
        if ($amount <= 0) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid amount'
            ]);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $wallet = $currentUser->getWallet();
        $wallet->setBalance($wallet->getBalance() + $amount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription("Top-up wallet")
            ->setStatus('completed')
            ->setType('top-up');

        $entityManagerInterface->persist($wallet);
        $entityManagerInterface->flush();

        return $this->json([
            'success' => true,
            'balance' => $currentUser->getWallet()->getBalance()
        ]);
    }
}
