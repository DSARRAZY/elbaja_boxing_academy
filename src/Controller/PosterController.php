<?php
/**
 * Auteur: Khaled Benharrat, Damien Sarrazy, Kevin Chalumeau
 * Date: 30/06/2020
 */

namespace App\Controller;

use App\Service\SearchPoster;
use App\Entity\Poster;
use App\Form\PosterType;
use App\Repository\PosterRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;
use Symfony\Component\HttpFoundation\File\Exception\ExtensionFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;

/**
 * @Route("/poster")
 */
class PosterController extends AbstractController
{
    /**
     * Returns all images in the library
     * @Route("/", name="poster_index", methods={"GET"})
     */
    public function index(PosterRepository $posterRepository): Response
    {
        return $this->render('poster/index.html.twig', [
            'posters' => $posterRepository->findBy([], ['date' => 'desc']),
        ]);
    }

    /**
     * Upload image to library, add unique name
     * @Route("/new", name="poster_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager): Response
    {
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
                return $this->redirectToRoute('poster_new');
            } catch (ExtensionFileException $e) {
                $this->addFlash('warning', 'Le format de votre fichier n\'est pas supporté.
                    Votre fichier doit être au format jpeg, jpg ou png.');
                return $this->redirectToRoute('poster_new');
            } catch (PartialFileException | NoFileException | CannotWriteFileException $e) {
                $this->addFlash('warning', 'Fichier non enregistré, veuillez réessayer.
                    Si le problème persiste, veuillez contacter l\'administrateur du site');
                return $this->redirectToRoute('poster_new');
            }
            $poster->setSlug($posterSlug);
            $entityManager->persist($poster);
            $entityManager->flush();
            return $this->redirectToRoute('poster_index');
        }

        return $this->render('poster/new.html.twig', [
            'poster' => $poster,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete an image and its related object
     * @Route("/{id}", name="poster_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(
        Request $request,
        Poster $poster,
        SearchPoster $remove,
        EntityManagerInterface $entityManager
    ): Response {
        
        if ($this->isCsrfTokenValid('delete'.$poster->getId(), $request->request->get('_token'))) {
            $remove->removeAssociate($poster);
            unlink($this->getParameter('upload_directory') . '/' . $poster->getSlug());
            $entityManager->remove($poster);
            $entityManager->flush();
        }

        return $this->redirectToRoute('poster_index');
    }
}
