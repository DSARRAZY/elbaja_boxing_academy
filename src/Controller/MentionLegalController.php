<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MentionLegalController extends AbstractController
{
    /**
     * Mention Legal page display
     * @Route("/mentionlegale",name="mentionlegale_index")
     * @return Response A response instance
     */
    public function mentionLegal() :Response
    {
        return $this->render('mentionlegale/index.html.twig');
    }
}
