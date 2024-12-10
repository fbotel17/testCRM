<?php


namespace App\Controller;

use App\Entity\Tache;
use App\Repository\UserRepository;
use App\Repository\TacheRepository;
use App\Repository\ClientRepository;
use App\Repository\ClientTaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(TacheRepository $tacheRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est valide (non obligatoire si la route est sécurisée)
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer les tâches de l'utilisateur
        $taches = $tacheRepository->findBy(['user' => $user, 'etat' => 'À faire']);
        //$taches = $entityManager->getRepository(Tache::class)->findBy(['user' => $user]);


        // Retourner une vue avec les tâches
        return $this->render('home/index.html.twig', [
            'taches' => $taches,
        ]);
    }
}

