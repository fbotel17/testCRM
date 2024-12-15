<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Societe;
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
        $user->setEmail('admin@example.com')
            ->setPrenom('Admin')
            ->setNom('Utilisateur')
            ->setRoles(['ROLE_ADMIN'])
            ->setDateCreation(new \DateTimeImmutable())
            ->setDateDerniereConnexion(null);

        // Hasher le mot de passe
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $manager->persist($user);

        // 2. Créer des tâches associées à l'utilisateur
        $taches = [
            [
                'titre' => 'Préparer proposition commerciale pour Amazon.',
                'description' => 'Ne pas oublier de préparer la proposition commerciale avec Amazon pour établir un plan futur.',
                'etat' => 'En cours'
            ],
            [
                'titre' => 'Préparer proposition commerciale pour Google.',
                'description' => 'Ne pas oublier de préparer la proposition commerciale avec Google pour établir un plan futur.',
                'etat' => 'À faire'
            ]
        ];

        foreach ($taches as $t) {
            $tache = new Tache();
            $tache->setTitre($t['titre'])
                ->setDescription($t['description'])
                ->setDateCreation(new \DateTimeImmutable())
                ->setDateEcheance((new \DateTimeImmutable())->modify('+7 days'))
                ->setEtat($t['etat'])
                ->setUserId($user); // Associer l'utilisateur
            $manager->persist($tache);
        }

        // 3. Créer une société
        $societe = new Societe();
        $societe->setNom('UPJV')
            ->setAdresse('7 rue de la Ville, Saint Quentin');
        $manager->persist($societe);

        // 4. Créer 100 clients associés à la société
        for ($i = 1; $i <= 100; $i++) {
            $client = new Client();
            $client->setNom("Client $i")
                ->setPrenom("Prenom $i")
                ->setEmail("client$i@example.com")
                ->setTelephone("0123456789$i") // Numéro unique
                ->setDateCreation(new \DateTimeImmutable())
                ->setSociete($societe); // Associer la société

            $client->addUser($user); // Associer l'utilisateur au client (méthode à vérifier dans votre entité)

            $manager->persist($client);
        }

        // 5. Sauvegarder tout dans la base
        $manager->flush();
    }
}
