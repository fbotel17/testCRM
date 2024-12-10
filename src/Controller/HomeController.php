<?php


namespace App\Controller;

use App\Entity\User;
use App\Entity\Tache;
use App\Repository\UserRepository;
use App\Repository\TacheRepository;
use App\Repository\ClientRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(ManagerRegistry $doctrine, TacheRepository $tacheRepository, ClientRepository $clientRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est valide (non obligatoire si la route est sécurisée)
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $user1 = $doctrine->getRepository(persistentObject: User::class)->findOneBy(['email' => $user->getUserIdentifier()]);


        // Récupérer les tâches de l'utilisateur
        $taches = $tacheRepository->findBy(['user' => $user1, 'etat' => 'À faire']);

        $clients = $user1->getClients();  // Méthode getClients() de l'entité User

        // Retourner une vue avec les tâches et les clients
        return $this->render('home/index.html.twig', [
            'taches' => $taches,
            'clients' => $clients,
        ]);
    }
}


