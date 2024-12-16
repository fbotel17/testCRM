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


    #[Route('/add_societe', name: 'add_societe', methods: ['POST'])]
    public function addSociete(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $nom = $request->get('nom');
        $adresse = $request->get('adresse');

        if (!$nom || !$adresse) {
            return new JsonResponse(['message' => 'Nom et adresse requis'], 400);
        }

        $societe = new Societe();
        $societe->setNom($nom)
            ->setAdresse($adresse);

        $em->persist($societe);
        $em->flush();

        return new JsonResponse(['id' => $societe->getId(), 'message' => 'Société créée avec succès'], 201);
    }

    #[Route('/societe/details', name: 'societe_details', methods: ['GET'])]
    public function getSocieteDetails(Request $request, SocieteRepository $societeRepository): JsonResponse
    {
        $nom = $request->query->get('nom');

        // Vérifier si le nom est fourni
        if (!$nom) {
            return new JsonResponse(['error' => 'Nom de la société non fourni'], 400);
        }

        $societe = $societeRepository->findOneBy(['nom' => $nom]);

        if (!$societe) {
            return new JsonResponse(['error' => 'Société introuvable'], 404);
        }

        return new JsonResponse([
            'nom' => $societe->getNom(),
            'adresse' => $societe->getAdresse(),
        ]);
    }

    #[Route('/client/details/{id}', name: 'client_details', methods: ['GET'])]
    public function getClientDetails(int $id, ClientRepository $clientRepository): JsonResponse
    {
        $client = $clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['error' => 'Client introuvable'], 404);
        }

        return new JsonResponse([
            'nom' => $client->getNom(),
            'prenom' => $client->getPrenom(),
            'email' => $client->getEmail(),
            'societe' => $client->getSociete() ? $client->getSociete()->getNom() : null,
            'telephone' => $client->getTelephone(),
            'adresse_societe' => $client->getSociete() ? $client->getSociete()->getAdresse() : null,
        ]);
    }

    #[Route('/client/edit/{id}', name: 'edit_client', methods: ['POST'])]
    public function editClient(Request $request, ManagerRegistry $doctrine, SocieteRepository $societeRepository, EntityManagerInterface $em): Response
    {
        $clientId = $request->request->get('id');
        $clientNom = $request->request->get('nom');
        $clientPrenom = $request->request->get('prenom');
        $clientEmail = $request->request->get('email');
        $clientSociete = $request->request->get('societe');
        $clientTelephone = $request->request->get('telephone');
        $adresse = $request->get('adresse'); // Nouvelle donnée pour l'adresse de la société

        

        $societe = $societeRepository->findOneBy(['nom' => $clientSociete]);

        if (!$societe) {
            // Si la société n'existe pas, la créer avec l'adresse
            $societe = new Societe();
            $societe->setNom($clientSociete);
            $societe->setAdresse($adresse); // Ajouter l'adresse à la société
            $em->persist($societe);
            $em->flush(); // Sauvegarder la société dans la base de données
        }


        $client = $doctrine->getRepository(Client::class)->find($clientId);
        if ($client) {
            $client->setNom($clientNom);
            $client->setPrenom($clientPrenom);
            $client->setEmail($clientEmail);
            $client->setSociete($societe);
            $client->setTelephone($clientTelephone);

            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            // Rediriger vers une page de confirmation ou la liste des clients
            return $this->redirectToRoute('app_home');
        }

        throw $this->createNotFoundException('Client non trouvé.');
    }



}


