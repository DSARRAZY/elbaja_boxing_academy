<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityController extends AbstractController
{
    /**
     * Activity page display
     * @Route("/activity",name="activity_index")
     * @return Response A response instance
     */
    public function activity() :Response
    {
        return $this->render('activity/index.html.twig');
    }
}
