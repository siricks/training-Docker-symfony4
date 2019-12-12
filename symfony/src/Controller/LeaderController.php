<?php

namespace App\Controller;

use App\Entity\Leader;
use App\Form\LeaderType;
use App\Repository\GameRepository;
use App\Repository\LeaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Leader controller
 *
 * @Route("/leader")
 */
class LeaderController extends AbstractController
{
    /**
     * Main page
     *
     * @Route("/", name="leader_index", methods={"GET"})
     * @param LeaderRepository $leaderRepository
     * @return Response
     */
    public function index(LeaderRepository $leaderRepository): Response
    {
        return $this->render('leader/index.html.twig', [
            'leaders' => $leaderRepository->findBy([], ['country' => 'ASC']),
        ]);
    }

    /**
     * Get 1 random leader to game
     * @Route("/get_random", name="leader_random", methods={"GET"})
     *
     * @param LeaderRepository $leaderRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function random(LeaderRepository $leaderRepository): Response
    {
        $leaders = $this->getRandomLeaders($leaderRepository);
        shuffle($leaders);
        $mainLeader = array_shift($leaders);
        $otherleaders = array_slice($leaders, 0, 3);
        return $this->render('leader/random.html.twig', [
            'random_leader' => $mainLeader,
            'leaders' => $otherleaders,
        ]);
    }

    /**
     * Set games count for all leaders
     *
     * @Route("/set-games-count", name="set_game_count_for_all", methods={"GET"})
     *
     * @param LeaderRepository $leaderRepository
     * @return Response
     */
    public function setLeaderGameCount(LeaderRepository $leaderRepository, GameRepository $gameRepository, EntityManagerInterface $entityManager)
    {
        $leaders = $leaderRepository->findAll();
        if(!$leaders) {
            return $this->redirect('leader_index');
        }
        foreach ($leaders as $leader) {
            $games = $gameRepository->findBy(['leader' => $leader]);
            $leader->setGamesCount(count($games));
            $entityManager->persist($leader);
        }

        $entityManager->flush();

        return $this->redirect('leader_index');
    }

    /**
     * Create leader
     *
     * @Route("/new", name="leader_new", methods={"GET","POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $leader = new Leader();
        $form = $this->createForm(LeaderType::class, $leader);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($leader);
            $entityManager->flush();

            return $this->redirectToRoute('leader_index');
        }

        return $this->render('leader/new.html.twig', [
            'leader' => $leader,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="leader_show", methods={"GET"})
     *
     * @param Leader $leader
     * @return Response
     */
    public function show(Leader $leader): Response
    {
        return $this->render('leader/show.html.twig', [
            'leader' => $leader,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="leader_edit", methods={"GET","POST"})
     *
     * @param Request $request
     * @param Leader $leader
     * @return Response
     */
    public function edit(Request $request, Leader $leader): Response
    {
        $form = $this->createForm(LeaderType::class, $leader);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('leader_index', [
                'id' => $leader->getId(),
            ]);
        }

        return $this->render('leader/edit.html.twig', [
            'leader' => $leader,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="leader_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param Leader $leader
     * @return Response
     */
    public function delete(Request $request, Leader $leader): Response
    {
        if ($this->isCsrfTokenValid('delete'.$leader->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($leader);
            $entityManager->flush();
        }

        return $this->redirectToRoute('leader_index');
    }

    /**
     * Get random leaders witout games or with min games count
     *
     * @param LeaderRepository $leaderRepository
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getRandomLeaders(LeaderRepository $leaderRepository)
    {
        $leaders = $leaderRepository->getFreeLeaders();
        if(!$leaders) {
            $minimumGames = $leaderRepository->getMinGamesCount();
            $leaders = $leaderRepository->getLeadersByGamesCount($minimumGames);
        }

        return $leaders;
    }


}
