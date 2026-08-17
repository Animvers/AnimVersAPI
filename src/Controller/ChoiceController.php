<?php

namespace App\Controller;

use App\Entity\Reponses;
use App\Entity\User;
use App\Repository\ChoiceRepository;
use App\Repository\SondageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChoiceController extends AbstractController
{
    public function __construct(
        private readonly SondageRepository $sondageRepo,
        private readonly ChoiceRepository $choiceRepo,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly NormalizerInterface $normalizer
    ) {}

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/choice/selected', name: 'choice_selected', methods: ['POST'])]
    public function choiceSelected(Request $request, #[CurrentUser] ?User $currentUser): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !is_array($data)) {
            return $this->json([
                'status' => 'error',
                'message' => 'JSON vide ou format invalide.'
            ], 400);
        }

        $payload = [];

        if (isset($data['sondage_id'])) {
            $payload['sondage_id'] = $data['sondage_id'];
        }

        if (isset($data['choice_id'])) {
            $payload['choice_id'] = $data['choice_id'];
        }

        if (!isset($payload['sondage_id']) || !isset($payload['choice_id'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'sondage_id et choice_id sont requis.'
            ], 400);
        }

        $sondage = $this->sondageRepo->find($payload['sondage_id']);
        $choice = $this->choiceRepo->find($payload['choice_id']);

        if (!$sondage) {
            return $this->json([
                'status' => 'error',
                'message' => 'Sondage inexistant.'
            ], 404);
        }

        if (!$choice) {
            return $this->json([
                'status' => 'error',
                'message' => 'Choix inexistant.'
            ], 404);
        }

        if ($choice->getWhichPoll() !== $sondage) {
            return $this->json([
                'status' => 'error',
                'message' => 'Ce choix n\'appartient pas à ce sondage.'
            ], 400);
        }

        $reponse = new Reponses();
        $reponse->setUserId($currentUser);
        $reponse->setChoiceId($choice);

        $violations = $this->validator->validate($reponse);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return $this->json([
                'status' => 'error',
                'message' => 'Données de la réponse invalides.',
                'errors' => $errors
            ], 400);
        }

        $this->em->persist($reponse);
        $this->em->flush();

        $dataResponse = $this->normalizer->normalize($reponse, null, ['groups' => ['reponse:post']]);

        return $this->json([
            'status' => 'success',
            'message' => 'Choix validé avec succès.',
            'data' => $dataResponse
        ], 201);
    }
}
