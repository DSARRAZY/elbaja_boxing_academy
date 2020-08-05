<?php
/**
 * Auteur: Khaled Benharrat, Damien Sarrazy, Kevin Chalumeau
 * Date: 30/06/2020
 */

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use App\Entity\Poster;
use App\Repository\PosterRepository;
use App\Service\FileUploader;
use App\Form\MemberType;
use App\Form\PosterType;
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
 * @Route("/member")
 */
class MemberController extends AbstractController
{
    /**
     * Returns all members
     * @Route("/", name="member_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(MemberRepository $memberRepository): Response
    {
        return $this->render('member/index.html.twig', [
            'members' => $memberRepository->findBy([], ['date' => 'desc']),
        ]);
    }

    /**
     * Returns a form to create a member and redirection to choose a poster
     * @Route("/new", name="member_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('member_poster_index', ['id' => $member->getId()]);
        }

        return $this->render('member/new.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Upload image to library, add unique name and add the image to the member
     * @Route("/new/{member}/addposter", name="member_add_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function newWithPoster(
        Request $request,
        FileUploader $fileUploader,
        Member $member,
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
                return $this->redirectToRoute('member_add_poster', ['member' => $member->getId()]);
            } catch (ExtensionFileException $e) {
                $this->addFlash('warning', 'Le format de votre fichier n\'est pas supporté.
                    Votre fichier doit être au format jpeg, jpg ou png.');
                return $this->redirectToRoute('member_add_poster', ['member' => $member->getId()]);
            } catch (PartialFileException | NoFileException | CannotWriteFileException $e) {
                $this->addFlash('warning', 'Fichier non enregistré, veuillez réessayer.
                    Si le problème persiste, veuillez contacter l\'administrateur du site');
                return $this->redirectToRoute('member_add_poster', ['member' => $member->getId()]);
            }
            $poster->setSlug($posterSlug);
            $entityManager->persist($poster);
            $member->setPoster($poster);
            $entityManager->flush();
            return $this->redirectToRoute('member_index');
        }

        return $this->render('member/add_poster.html.twig', [
            'poster' => $poster,
            'form' => $form->createView(),
        ]);
    }

    /**
     * List of all posters, choice of a poster for a member
     * @Route("/new/{id}", name="member_poster_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function choicePoster(Member $member, PosterRepository $posterRepository): Response
    {
        return $this->render('member/poster.html.twig', [
            'posters' => $posterRepository->findBy([], ['date' => 'desc']),
            'member' => $member,
        ]);
    }

    /**
     * Add a poster to a member and redirection to list of poster
     * @Route("/new/{member}/poster/{poster}", name="member_new_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function addPoster(Member $member, Poster $poster, EntityManagerInterface $entityManager): Response
    {
        $member->setPoster($poster);
        $entityManager->flush();
        
        return $this->redirectToRoute('member_index');
    }

    /**
     * Returns a member and a form to edit the member
     * @Route("/{id}/edit", name="member_edit", methods={"GET","POST"},requirements={"id": "\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('member_index');
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete a member
     * @Route("/{id}", name="member_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('member_index');
    }
}
