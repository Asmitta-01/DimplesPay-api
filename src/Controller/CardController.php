<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\Transaction;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class CardController extends AbstractController
{
    #[Route('/api/nfc/activate', methods: ['POST'])]
    public function activate(EntityManagerInterface $entityManagerInterface, CardRepository $cardRepository): JsonResponse
    {
        /** @var Card $card */
        $card = $cardRepository->findOneBy(['user' => $this->getUser()]);
        if ($card == null) {
            return $this->json(['message' => 'Card not found'], 404);
        }

        $card->activate();
        $entityManagerInterface->persist($card);
        $entityManagerInterface->flush();

        return $this->json(['card_id' => $card->getId(), 'status' => 'active']);
    }

    #[Route('/api/nfc/topup', methods: ['POST'])]
    public function topup(
        EntityManagerInterface $entityManagerInterface,
        CardRepository $cardRepository,
        Request $request
    ): JsonResponse {
        /** @var Card $card */
        $card = $cardRepository->findOneBy(['user' => $this->getUser()]);
        if ($card == null) {
            return $this->json(['message' => 'Card not found'], 404);
        }

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
        if ($amount >= $wallet->getBalance()) {
            return $this->json([
                'success' => false,
                'message' => 'Insufficient funds'
            ]);
        }

        $wallet->setBalance($wallet->getBalance() - $amount);
        $card->setBalance($card->getBalance() + $amount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription("Top-up card")
            ->setStatus('completed')
            ->setType('transfer')
            ->setUser($currentUser);

        $entityManagerInterface->persist($transaction);
        $entityManagerInterface->persist($wallet);
        $entityManagerInterface->persist($card);
        $entityManagerInterface->flush();

        return $this->json(['success' => true, 'card_balance' => $card->getBalance()]);
    }

    #[Route('/api/nfc/deduct', methods: ['POST'])]
    public function deduct(
        EntityManagerInterface $entityManagerInterface,
        CardRepository $cardRepository,
        Request $request
    ): JsonResponse {
        /** @var Card $card */
        $card = $cardRepository->findOneBy(['user' => $this->getUser()]);
        if ($card == null) {
            return $this->json(['message' => 'Card not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (array_key_exists('amount', $data) == false) {
            return $this->json([
                'success' => false,
                'message' => 'Amount is required'
            ]);
        }
        if (array_key_exists('pinCode', $data) == false) {
            return $this->json([
                'success' => false,
                'message' => 'Pin code is required'
            ]);
        }

        $amount = floatval($data['amount']) ?? 0;
        $pinCode = intval($data['pinCode']) ?? 0;
        if ($amount <= 0) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid amount'
            ]);
        }
        if ($pinCode != $card->getPinCode()) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid Pin code'
            ]);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($amount >= $card->getBalance()) {
            return $this->json([
                'success' => false,
                'message' => 'Insufficient funds'
            ]);
        }

        $card->setBalance($card->getBalance() - $amount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription("Deducted from card")
            ->setStatus('completed')
            ->setType('payment')
            ->setUser($currentUser);

        $entityManagerInterface->persist($transaction);
        $entityManagerInterface->persist($card);
        $entityManagerInterface->flush();

        return $this->json(['success' => true, 'card_balance' => $card->getBalance()]);
    }
}
