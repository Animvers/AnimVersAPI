<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Sondage;
use App\Entity\User;
use App\Repository\SondageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SondageController extends AbstractController
{
    public function __construct(
        private readonly SondageRepository $sondageRepo,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly NormalizerInterface $normalizer
    ) {}

//    #[IsGranted('ROLE_ADMIN')]
    #[Route('/sondage/create', name: 'sondage_create', methods: ['POST'])]
    public function sondageCreate(Request $request, #[CurrentUser] ?User $currentUser): JsonResponse
    {
        $newSondage = new Sondage();

        $payload = $request->request->all();

        if (isset($payload['title']))
        {
            $newSondage->setTitle($payload['title']);
        }

        if (isset($payload['question']))
        {
            $newSondage->setQuestion($payload['question']);
        }

        if (isset($payload['category_name']))
        {
            $newSondage->setCategoryName($payload['category_name']);
        }

        $newSondage->setIsActive(false);
        $newSondage->setWhoMakeIt($currentUser);
        $newSondage->setCreateAt(new \DateTimeImmutable());

        $file = $request->files->get('imageUrl');

        if ($file)
        {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/sondages';
            $newFilename = uniqid() . '.' . $file->guessExtension();

            try {
                $file->move($uploadDir, $newFilename);
                $newSondage->setImageUrl($newFilename);
            } catch (FileException $e) {
                return $this->json([
                    'status' => 'error',
                    'message' => "Erreur lors de l'upload de l'image."
                ], 500);
            }
        }

        $violations = $this->validator->validate($newSondage);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return $this->json([
                'status' => 'error',
                'message' => 'Données du sondage invalides.',
                'errors' => $errors
            ], 400);
        }

        $choicesData = [];
        if (isset($payload['choices'])) {
            $choicesData = json_decode($payload['choices'], true) ?? [];
        }

        if (empty($choicesData) || !is_array($choicesData)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Un sondage doit contenir au moins un choix.'
            ], 400);
        }

        foreach ($choicesData as $choiceLabel) {

            if (!empty($choiceLabel)) {

                $choice = new Choice();
                $choice->setLabel($choiceLabel);
                $choice->setWhichPoll($newSondage);

                $choiceViolations = $this->validator->validate($choice);

                if (count($choiceViolations) > 0) {
                    $choiceErrors = [];
                    foreach ($choiceViolations as $violation) {
                        $choiceErrors[$violation->getPropertyPath()][] = $violation->getMessage();
                    }

                    return $this->json([
                        'status' => 'error',
                        'message' => 'Un ou plusieurs choix sont invalides.',
                        'errors' => $choiceErrors
                    ], 400);
                }

                $this->em->persist($choice);
            }
        }

        $this->em->persist($newSondage);
        $this->em->flush();

        $data = $this->normalizer->normalize($newSondage, null, ['groups' => ['sondage:read']]);

        return $this->json([
            'data' => $data,
            'message' => 'Sondage créé avec succès.'
        ], 201);
    }


    #[Route('/sondage/get-all', name: 'sondages_get_all', methods: ['GET'])]
    public function sondageGetAll(): JsonResponse
    {
        $sondages = $this->sondageRepo->findBy(['isActive' => true]);

        if (empty($sondages)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Pas de sondages disponibles'
            ], 404);
        }

        $data = $this->normalizer->normalize($sondages, null, ['groups' => ['sondage:read']]);

        return $this->json([
            'data' => $data,
            'status' => 'success',
            'message' => 'Sondages récupérés avec succès'
        ], 200);
    }
}
