<?php
/**
 * Auteur: Khaled Benharrat, Damien Sarrazy, Kevin Chalumeau
 * Date: 30/06/2020
 */

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Poster;
use App\Repository\PosterRepository;
use App\Service\FileUploader;
use App\Form\PosterType;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;

/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    /**
     * Returns all events
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findBy([], ['date' => 'desc']),
        ]);
    }

    /**
     * Returns a form to create an event and redirection to choose a poster
     * @Route("/new", name="event_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_poster_index', ['id' => $event->getId()]);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Upload image to library, add unique name and add the image to the event
     * @Route("/new/{event}/addposter", name="event_add_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function newWithPoster(
        Request $request,
        FileUploader $fileUploader,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response {

        $poster = new Poster();
        $form = $this->createForm(PosterType::class, $poster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $posterFile */
            $posterFile = $form->get('poster_img')->getData();
            try {
                $posterSlug = $fileUploader->upload($posterFile, $poster->getFileName());
            } catch (IniSizeFileException | FormSizeFileException $e) {
                $this->addFlash('warning', 'Votre fichier est trop lourd, il ne doit pas dépasser 1Mo.');
                return $this->redirectToRoute('event_add_poster', ['event' => $event->getId()]);
            } catch (ExtensionFileException $e) {
                $this->addFlash('warning', 'Le format de votre fichier n\'est pas supporté.
                    Votre fichier doit être au format jpeg, jpg ou png.');
                return $this->redirectToRoute('event_add_poster', ['event' => $event->getId()]);
            } catch (PartialFileException | NoFileException | CannotWriteFileException $e) {
                $this->addFlash('warning', 'Fichier non enregistré, veuillez réessayer.
                    Si le problème persiste, veuillez contacter l\'administrateur du site');
                return $this->redirectToRoute('event_add_poster', ['event' => $event->getId()]);
            }
            $poster->setSlug($posterSlug);
            $entityManager->persist($poster);
            $event->setPoster($poster);
            $entityManager->flush();
            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/add_poster.html.twig', [
            'poster' => $poster,
            'form' => $form->createView(),
        ]);
    }

    /**
     * List of all posters, choice of a poster for an event
     * @Route("/new/{id}", name="event_poster_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function choicePoster(Event $event, PosterRepository $posterRepository): Response
    {
        return $this->render('event/poster.html.twig', [
            'posters' => $posterRepository->findBy([], ['date' => 'desc']),
            'event' => $event,
        ]);
    }

    /**
     * Add a poster to an event and redirection to list of poster
     * @Route("/new/{event}/poster/{poster}", name="event_new_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function addPoster(Event $event, Poster $poster, EntityManagerInterface $entityManager): Response
    {
        $event->setPoster($poster);
        $entityManager->flush();
        
        return $this->redirectToRoute('event_index');
    }

    /**
     * Return an event
     * @Route("/{id}", name="event_show", methods={"GET"})
     */
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Returns an event and a form to edit the event
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"},requirements={"id": "\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete an event
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}
