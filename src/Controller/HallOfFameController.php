<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MemberRepository;

class HallOfFameController extends AbstractController
{
    /**
     * Hall of Fame page display
     * Return the heroes members
     * @Route("/hall",name="hall_index")
     * @return Response A response instance
     */
    public function hall(MemberRepository $member) :Response
    {
        return $this->render('hall/index.html.twig', [
            'members' => $member->findByCategory(['hero'], ['date' => 'desc'])
        ]);
    }
}
