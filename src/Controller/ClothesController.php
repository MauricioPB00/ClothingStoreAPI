<?php

namespace App\Controller;

use App\Entity\Clothes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ClothesController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/api/clothes", name="clothes_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['size'], $data['resale'], $data['bought'])) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Dados inválidos',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $clothes = new Clothes();
        $clothes->setName($data['name']);
        $clothes->setSize($data['size']);
        $clothes->setResale($data['resale']);
        $clothes->setBought($data['bought']);

        try {
            $this->em->persist($clothes);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Erro ao salvar no banco de dados',
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retorna sucesso
        return new JsonResponse([
            'error' => false,
            'message' => 'Item de roupa criado com sucesso',
        ], JsonResponse::HTTP_CREATED);
    }


    /**
     * @Route("/api/clothes/last", name="clothing_get_last", methods={"GET"})
     */
    public function getLastClothing(): JsonResponse
    {
        $lastClothing = $this->em->getRepository(Clothes::class)->findOneBy([], ['id' => 'DESC']);

        if (!$lastClothing) {
            return new JsonResponse(['message' => 'No clothing item found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $nextId = $lastClothing->getId() + 1;

        return new JsonResponse([
            'nextId' => $nextId,
        ], JsonResponse::HTTP_OK);
    }
}
