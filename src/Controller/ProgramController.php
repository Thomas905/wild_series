<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Service\Slugify;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/program", name="program_")
 */
class ProgramController extends AbstractController
{


    /**
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        // Create a new program Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get data from HTTP request
        $form->handlerequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());

            // Persist Product Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);
            // Finally redirect to categories list
            return $this->redirectToRoute('program_index');
        }
        // Render the form 
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }


    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render(
            'program/index.html.twig',
            ['programs' => $programs]
        );
    }

    /**
     * Getting a program by id
     *
     * @Route("/{slug}", methods={"GET"}, name="show")
     * @return Response
     */
    public function show(Program $program): Response
    {
        return $this->render('program/show.html.twig', [
            'program' => $program,
        ]);
    }

    /**
     * @Route("/{program_id}/season/{season_id}", name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter("season", class="App\Entity\season", options={"mapping": {"season_id": "id"}})
     */
    public function showSeason(Program $program, Season $season): Response
    {
        return $this->render('Program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
        ]);
    }

    /**
     * @Route("/{program_id}/season/{season_id}/episode/{episode_id}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter("season", class="App\Entity\season", options={"mapping": {"season_id": "id"}})
     * @ParamConverter("episode", class="App\Entity\episode", options={"mapping": {"episode_id": "id"}})
     */
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request): Response
    {
        $comment = new Comment;
        
        $form = $this->createForm(CommentType::class, $comment);
        // Get data from HTTP request
        $form->handlerequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setEpisode($episode);
            $comment->setAuthor($this->getUser());
            // Persist Product Object
            $entityManager->persist($comment);
            // Flush the persisted object
            $entityManager->flush();
            return $this->redirectToRoute('episode_index');
        }
        return $this->render('/program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'comments' => $comment,
            "form" => $form->createView(),
        ]);
    }
    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        // Check wether the logged in user is the owner of the program
        if (!($this->getUser() == $program->getOwner()) && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the program!');
        }

        return $this->render('/program/edit.html.twig', [
            'program' => $program,
            "form" => $form->createView(),
        ]);
    }

    /**
     * Correspond à la route /program/{program_id}/seasons/{season_id}/episode/{episode_id}/comment/{comment_id}/delete et au name "program_comment_delete"
     * @Route("/{program_id}/seasons/{season_id}/episode/{episode_id}/comment/{comment_id}", requirements={"program_id"="\d+", "season_id"="\d+", "episode_id" = "\d+"}, methods={"GET", "POST"}, name="comment_delete")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"season_id": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episode_id": "id"}})
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"comment_id": "id"}})
     */
    public function deleteComment(Program $program, Season $season, Episode $episode, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == $comment->getAuthor() || in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }
        
        return $this->redirectToRoute('program_episode_show', ['program_id' => $program->getId(), "season_id" => $season->getId(), 'episode_id' => $episode->getId()], Response::HTTP_SEE_OTHER);
    }

}
