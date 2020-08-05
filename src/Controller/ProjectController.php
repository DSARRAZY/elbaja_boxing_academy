<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Poster;
use App\Repository\PosterRepository;
use App\Form\ProjectType;
use App\Service\FileUploader;
use App\Form\PosterType;
use App\Repository\ProjectRepository;
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
 * @Route("/project")
 */
class ProjectController extends AbstractController
{
    /**
     * Returns all projects
     * @Route("/", name="project_index", methods={"GET"})
     */
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $projectRepository->findBy([], ['date' => 'desc']),
        ]);
    }

    /**
     * Returns a form to create a project and redirection to choose a poster
     * @Route("/new", name="project_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_poster_index', ['id' => $project->getId()]);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Upload image to library, add unique name and add the image to the project
     * @Route("/new/{project}/addposter", name="project_add_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function newWithPoster(
        Request $request,
        FileUploader $fileUploader,
        Project $project,
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
                return $this->redirectToRoute('project_add_poster', ['project' => $project->getId()]);
            } catch (ExtensionFileException $e) {
                $this->addFlash('warning', 'Le format de votre fichier n\'est pas supporté.
                    Votre fichier doit être au format jpeg, jpg ou png.');
                return $this->redirectToRoute('project_add_poster', ['project' => $project->getId()]);
            } catch (PartialFileException | NoFileException | CannotWriteFileException $e) {
                $this->addFlash('warning', 'Fichier non enregistré, veuillez réessayer.
                    Si le problème persiste, veuillez contacter l\'administrateur du site');
                return $this->redirectToRoute('project_add_poster', ['project' => $project->getId()]);
            }
            $poster->setSlug($posterSlug);
            $entityManager->persist($poster);
            $project->setPoster($poster);
            $entityManager->flush();
            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/add_poster.html.twig', [
            'poster' => $poster,
            'form' => $form->createView(),
        ]);
    }

    /**
     * List of all posters, choice of a poster for a project
     * @Route("/new/{id}", name="project_poster_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function choicePoster(Project $project, PosterRepository $posterRepository): Response
    {
        return $this->render('project/poster.html.twig', [
            'posters' => $posterRepository->findBy([], ['date' => 'desc']),
            'project' => $project,
        ]);
    }

    /**
     * Add a poster to a project and redirection to list of project
     * @Route("/new/{project}/poster/{poster}", name="project_new_poster", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function addPoster(Project $project, Poster $poster, EntityManagerInterface $entityManager): Response
    {
        $project->setPoster($poster);
        $entityManager->flush();
        
        return $this->redirectToRoute('project_index');
    }

    /**
     * Return a project
     * @Route("/{id}", name="project_show", methods={"GET"})
     */
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    /**
     * Returns a project and a form to edit the project
     * @Route("/{id}/edit", name="project_edit", methods={"GET","POST"},requirements={"id": "\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('project_index');
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete a project
     * @Route("/{id}", name="project_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('project_index');
    }
}
