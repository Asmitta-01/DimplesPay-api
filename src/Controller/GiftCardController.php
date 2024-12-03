<?php


namespace App\Controller;

use App\Entity\User;
use App\Entity\GiftCard;
use App\Entity\Transaction;
use App\Repository\GiftCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/gift-cards')]
class GiftCardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GiftCardRepository $giftCardRepository,
        private SerializerInterface $serializer,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $giftCards = $this->giftCardRepository->findAvailableGiftCards();

        return $this->json($giftCards, Response::HTTP_OK, [], ['groups' => ['gift_card:read']]);
    }

    #[Route('/purchase', methods: ['POST'])]
    public function purchase(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cardId = $data['cardId'] ?? null;

        if (!$cardId) {
            return $this->json(['message' => 'Card ID is required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var GiftCard $giftCard */
        $giftCard = $this->giftCardRepository->find($cardId);
        if (!$giftCard) {
            return $this->json(['message' => 'Gift card not found'], Response::HTTP_NOT_FOUND);
        }

        if ($giftCard->isRedeemed()) {
            return $this->json(['message' => 'Gift card already redeemed'], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $currentUser */
            $currentUser = $this->getUser();
            $wallet = $currentUser->getWallet();
            if ($giftCard->getAmount() > $wallet->getBalance()) {
                return $this->json(['message' => 'insufficient funds'], Response::HTTP_BAD_REQUEST);
            }
            $wallet->setBalance($wallet->getBalance() - $giftCard->getAmount());

            $giftCard->setOwner($this->getUser());

            $transaction = new Transaction();
            $transaction->setAmount($giftCard->getAmount())
                ->setDescription("Purchased gift card")
                ->setStatus('completed')
                ->setType('gift-card')
                ->setUser($currentUser);

            $this->entityManager->persist($transaction);
            $this->entityManager->persist($giftCard);
            $this->entityManager->persist($wallet);
            $this->entityManager->flush();

            return $this->json(['success' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Transaction failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/redeem', methods: ['POST'])]
    public function redeem(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? null;

        if (!$code) {
            return $this->json(['message' => 'Code is required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var GiftCard $giftCard */
        $giftCard = $this->giftCardRepository->findOneBy(['code' => $code]);
        if (!$giftCard) {
            return $this->json(['message' => 'Invalid gift card code'], Response::HTTP_NOT_FOUND);
        }

        if ($giftCard->isRedeemed()) {
            return $this->json(['message' => 'Gift card already redeemed'], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $currentUser */
            $currentUser = $this->getUser();
            $wallet = $currentUser->getWallet();
            $wallet->setBalance($wallet->getBalance() + $giftCard->getAmount());

            $giftCard->setRedeemed(true);

            $transaction = new Transaction();
            $transaction->setAmount($giftCard->getAmount())
                ->setDescription("Redeemed gift card")
                ->setStatus('completed')
                ->setType('gift-card')
                ->setUser($currentUser);

            $this->entityManager->persist($wallet);
            $this->entityManager->persist($giftCard);
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->json(['success' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Redemption failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
