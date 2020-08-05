<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Poster;
use App\Form\PartnerType;
use App\Service\FileUploader;
use App\Form\PosterType;
use App\Repository\PartnerRepository;
use App\Repository\PosterRepository;
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
 * @Route("/partner")
 */
class PartnerController extends AbstractController
{
    /**
     * Returns all partners
     * @Route("/", name="partner_index", methods={"GET"})
     */
    public function index(PartnerRepository $partnerRepository): Response
    {
        return $this->render('partner/index.html.twig', [
            'partners' => $partnerRepository->findAll(),
        ]);
    }

    /**
     * Returns a form to create an partner and redirection to choose a poster
     * @Route("/new", name="partner_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $partner = new Partner();
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partner);
            $entityManager->flush();

            return $this->redirectToRoute('partner_poster_index', ['id' => $partner->getId()]);
        }

        return $this->render("partner/new.html.twig", [
            'partner' => $partner,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Upload image to library, add unique name and add the image to the partner
     * @Route("/new/{partner}/addposter", name="partner_add_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function newWithPoster(
        Request $request,
        FileUploader $fileUploader,
        Partner $partner,
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
                return $this->redirectToRoute('partner_add_poster', ['partner' => $partner->getId()]);
            } catch (ExtensionFileException $e) {
                $this->addFlash('warning', 'Le format de votre fichier n\'est pas supporté.
                    Votre fichier doit être au format jpeg, jpg ou png.');
                return $this->redirectToRoute('partner_add_poster', ['partner' => $partner->getId()]);
            } catch (PartialFileException | NoFileException | CannotWriteFileException $e) {
                $this->addFlash('warning', 'Fichier non enregistré, veuillez réessayer.
                    Si le problème persiste, veuillez contacter l\'administrateur du site');
                return $this->redirectToRoute('partner_add_poster', ['partner' => $partner->getId()]);
            }
            $poster->setSlug($posterSlug);
            $entityManager->persist($poster);
            $partner->setPoster($poster);
            $entityManager->flush();
            return $this->redirectToRoute('partner_index');
        }

        return $this->render('partner/add_poster.html.twig', [
            'poster' => $poster,
            'form' => $form->createView(),
        ]);
    }

    /**
     * List of all posters, choice of a poster for an partner
     * @Route("/new/{id}", name="partner_poster_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function choicePoster(Partner $partner, PosterRepository $posterRepository): Response
    {
        return $this->render('partner/poster.html.twig', [
            'posters' => $posterRepository->findBy([], ['date' => 'desc']),
            'partner' => $partner,
        ]);
    }

    /**
     * Add a poster to an partner and redirection to list of poster
     * @Route("/new/{partner}/poster/{poster}", name="partner_new_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function addPoster(Partner $partner, Poster $poster, EntityManagerInterface $entityManager): Response
    {
        $partner->setPoster($poster);
        $entityManager->flush();
        
        return $this->redirectToRoute('partner_index');
    }

    /**
     * delete an partner
     * @Route("/{id}", name="partner_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getId(), $request->request->get('_token'))) {
            $entityManager->remove($partner);
            $entityManager->flush();
        }

        return $this->redirectToRoute('partner_index');
    }
}
