<?php

namespace App\Controller;

use App\Entity\LuckyNumber;
use App\Form\LuckyNumberType;
use App\Repository\LuckyNumberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lucky/number')]
final class LuckyNumberController extends AbstractController
{
    #[Route(name: 'app_lucky_number_index', methods: ['GET'])]
    public function index(LuckyNumberRepository $luckyNumberRepository): Response
    {
        return $this->render('lucky_number/index.html.twig', [
            'lucky_numbers' => $luckyNumberRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_lucky_number_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $luckyNumber = new LuckyNumber();
        $form = $this->createForm(LuckyNumberType::class, $luckyNumber);
        $form->handleRequest($request);
        //dd($form->getErrors(true));


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($luckyNumber);
            $entityManager->flush();

            return $this->redirectToRoute('app_lucky_number_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lucky_number/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lucky_number_show', methods: ['GET'])]
    public function show(LuckyNumber $luckyNumber): Response
    {
        return $this->render('lucky_number/show.html.twig', [
            'lucky_number' => $luckyNumber,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lucky_number_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LuckyNumber $luckyNumber, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LuckyNumberType::class, $luckyNumber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lucky_number_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lucky_number/edit.html.twig', [
            'lucky_number' => $luckyNumber,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lucky_number_delete', methods: ['POST'])]
    public function delete(Request $request, LuckyNumber $luckyNumber, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$luckyNumber->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($luckyNumber);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lucky_number_index', [], Response::HTTP_SEE_OTHER);
    }
}
