<?php


namespace App\Controller;

use App\Entity\User;
use App\Entity\Tache;
use App\Entity\Client;
use App\Entity\Societe;
use App\Repository\UserRepository;
use App\Repository\TacheRepository;
use App\Repository\ClientRepository;
use App\Repository\SocieteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $societes = $doctrine->getRepository(persistentObject: Societe::class)->findAll();


        // Retourner une vue avec les tâches et les clients
        return $this->render('home/index.html.twig', [
            'taches' => $taches,
            'clients' => $clients,
            'societes' => $societes, // Ajouter la variable 'societes' à la vue
        ]);
    }

    #[Route('/add_client', name: 'add_client', methods: ['POST'])]
public function addClient(Request $request, EntityManagerInterface $em, SocieteRepository $societeRepository)
{
    // Récupérer les données envoyées dans la requête
    $nom = $request->get('nom');
    $prenom = $request->get('prenom');
    $email = $request->get('email');
    $societeName = $request->get('societe');
    $telephone = $request->get('telephone');
    $adresse = $request->get('adresse'); // Nouvelle donnée pour l'adresse de la société

    // Vérifier si la société existe
    $societe = $societeRepository->findOneBy(['nom' => $societeName]);

    if (!$societe) {
        // Si la société n'existe pas, la créer avec l'adresse
        $societe = new Societe();
        $societe->setNom($societeName);
        $societe->setAdresse($adresse); // Ajouter l'adresse à la société
        $em->persist($societe);
        $em->flush(); // Sauvegarder la société dans la base de données
    }

    // Ajouter un nouveau client et l'associer à la société
    $client = new Client();
    $client->setNom($nom)
           ->setPrenom($prenom)
           ->setEmail($email)
           ->setTelephone($telephone)
           ->setDateCreation(new \DateTime())
           ->setSociete($societe);  // Lier le client à la société existante ou nouvellement créée

    // Associer le client à l'utilisateur connecté
    $user = $this->getUser(); // Récupérer l'utilisateur connecté
    if ($user) {
        $client->addUser($user); // Associer le client à l'utilisateur
    }

    $em->persist($client);
    $em->flush(); // Sauvegarder le client dans la base de données

    // Retourner une réponse (ici, une redirection vers la page d'accueil ou une autre page)
    return $this->redirectToRoute('app_home');  // Redirige vers la page d'accueil ou la page des clients
}


    #[Route('/api/create-societe', name: 'create_societe', methods: ['POST'])]
    public function createSociete(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $societeName = $data['name'];
        $adresse = $data['adresse'];

        // Créer une nouvelle société
        $societe = new Societe();
        $societe->setNom($societeName);
        $societe->setAdresse($adresse);

        // Sauvegarder dans la base de données
        $em->persist($societe);
        $em->flush();

        // Répondre avec un message de succès
        return new JsonResponse(['success' => true]);
    }
}


