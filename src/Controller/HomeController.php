<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MemberRepository;

class HomeController extends AbstractController
{
    /**
     * Home page display
     * Return the coach members
     * @Route("/",name="home_index")
     * @return Response A response instance
     */
    public function index(MemberRepository $member) :Response
    {
        return $this->render('index.html.twig', [
            'members' => $member->findByCategory(['coach'], ['date' => 'desc']),
        ]);
    }
}
