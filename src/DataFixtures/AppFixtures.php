<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Tache;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Créer un utilisateur
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setPrenom('Admin');
        $user->setNom('Utilisateur');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setDateCreation(new \DateTimeImmutable());
        $user->setDateDerniereConnexion(null);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        // 2. Créer un client
        $client = new Client();
        $client->setNom('Doe');
        $client->setPrenom('John');
        $client->setEmail('john.doe@example.com');
        $client->setTelephone('0123456789');
        $client->setAdresseSociete('123 Rue Exemple, 75000 Paris');
        $client->setDateCreation(new \DateTimeImmutable());
        $client->setCompany(null); // Pas de société associée pour l'exemple

        // Lier l'utilisateur au client
        $client->addUserId($user); // Ajoute l'utilisateur à la collection user_id

        $manager->persist($client);

        // 3. Créer une tâche pour ce client
        $tache1 = new Tache();
        $tache1->setTitre('Préparer proposition commerciale pour amazon.');
        $tache1->setDescription('Ne pas oublier de préparer la propososition commerciale avec amazon pour établir un plan futur.');
        $tache1->setDateCreation(new \DateTimeImmutable());
        $tache1->setDateEcheance((new \DateTimeImmutable())->modify('+7 days'));
        $tache1->setEtat('En cours'); // Exemple d'état
        $tache1->setUserId($user); // Associer la tâche au client

        

        $manager->persist($tache1);


        $tache2 = new Tache();
        $tache2->setTitre('Préparer proposition commerciale pour google.');
        $tache2->setDescription('Ne pas oublier de préparer la propososition commerciale avec google pour établir un plan futur.');
        $tache2->setDateCreation(new \DateTimeImmutable());
        $tache2->setDateEcheance((new \DateTimeImmutable())->modify('+7 days'));
        $tache2->setEtat('À faire'); // Exemple d'état
        $tache2->setUserId($user); // Associer la tâche au client

        

        $manager->persist($tache2);

        // 4. Sauvegarder tout dans la base
        $manager->flush();
    }

}

