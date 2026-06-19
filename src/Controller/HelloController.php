<?php

namespace App\Controller;

use App\Repository\LuckyNumberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HelloController extends AbstractController
{
    private LuckyNumberRepository $luckyNumberRepository;

    public function __construct(LuckyNumberRepository $luckyNumberRepository)
    {
        $this->luckyNumberRepository = $luckyNumberRepository;
    }

    #[Route(path: '/hello')]
    public function hello(Request $request): Response
    {
        $reuqestIp = $request->getClientIp();
        $httpMethod = $request->getMethod();
        return new Response("Hello. $reuqestIp. Current method: $httpMethod");
    }

    #[Route('/hello/{name}')]
    public function greet(string $name, Request $request): Response
    {
        $baseUri = $request->getUri();
        return new Response("Hello $name. BaseUri: $baseUri");
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/hello/lucky/number', name: "app_generate_lucky_number")]
    public function generateLuckyNumber(): Response 
    {
        $luckyNumber = random_int(0, 100);
        $this->luckyNumberRepository->saveLuckyNumber($luckyNumber);
        return $this->render(
            'HelloController/index.html.twig',
            [ 'luckyNumber' => $luckyNumber ]
        );
    }

    #[Route('/hello/lucky/number/odd/{maxValue}')]
    public function getOddLuckyNumbers(int $maxValue = 50): Response
    {
        $luckyNumberArr = $this->luckyNumberRepository->findAllOdds($maxValue);
        return $this->render(
            'HelloController/list.html.twig',
            [ 'luckyNumbers' => $luckyNumberArr ]
        );
    }
}
