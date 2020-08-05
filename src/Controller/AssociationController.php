<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssociationController extends AbstractController
{
    /**
     * Association page display
     * @Route("/association",name="association_index")
     * @return Response A response instance
     */
    public function association() :Response
    {
        return $this->render('association/index.html.twig');
    }
}
