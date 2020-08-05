<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PressController extends AbstractController
{
    /**
     * Press page display
     * @Route("/press",name="press_index")
     * @return Response A response instance
     */
    public function press() :Response
    {
        return $this->render('press/index.html.twig');
    }
}
