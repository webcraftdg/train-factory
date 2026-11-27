<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Block;
use App\Entity\Exercise;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\ExerciseRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPaswwordHasher,
        private ExerciseRepository $exerciseRepository,
        private ActivityRepository $activityRepository
        )
    {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $userAdmin = new User();
        $userAdmin->setEmail('admin@webcraftdg.fr')
        ->setFirstname('admin')
        ->setLastname('admin')
        ->setPassword($this->userPaswwordHasher->hashPassword($userAdmin, 'redcat'))
        ->setRoles([User::ROLE_SUPER_ADMIN]);
        $manager->persist($userAdmin);

        $blocs = [

        ];

        $exercises= [
            'Meneur' => 'Déplacement en garde, je suis le meneur',
            'Suiveur' => 'Déplacement en garde, je suis le suiveur',
            'Cible Poing' => 'Déplacement, présenter les cibles de directs Avant / Arrière chacun son tour',
            'Cible pied' => 'Déplacement, ptésenter les cibles direct Av/AR FIG + Fouette AV/AR Médian',
            'Cible pied/poings' => 'Déplacement, Cibles, direct Fig + Fouettés  + Chassé Frontatl Médian',
            'Attaque 1' => 'D AV + FOUET AR LB',
            'Attaque 2' => 'D AV + FOUET AR LB/MED',
            'Attaque 3' => 'FOUET AV LB + D AR FIG',
            'Attaque 4' => 'FOUET AV LB / MED + D AR FIG',
            'Attaque 5' => 'PIEDS ( Fouet / CHASS / REV) avec TOURNANT si possible',
            'Riposte 1' => '
                organisation en défense (parade
                chassée, bloquée ou en
                protection) je ne suis pas touché
                : Riposte sur le retrait de jambe
                FOUET AV LB + TOURNANT
                CHASS AR LB',
            'Riposte 2' => 'organisation en défense (parade
                chassée, bloquée ou en
                protection) je ne suis pas touché
                : Riposte sur le retrait de jambe/
                Parade Bloquée FOUET AV LB +
                TOURNANT CHASS AR LB/MED',
            'Riposte 3' => 'organisation en défense (parade
                chassée, bloquée ou en
                protection) + Revert AR Tournant
                LB + D AV FIG',
            'Riposte 4' => 'organisation en défense (parade
                chassée, bloquée ou en
                FOUET AV LB / MED + D AR FIG
                protection) + Revert AR Tournant
                LB / MED / FIG + D AV FIG',
            'Riposte 5' => 'Poings',
            'Libre' => 'Libre',
            'Pour tous' => 'Etirements, bas du corps,
                haut du corps / musculation,
                Shadow boxing lent bras + jambe, Shadow boxing bras'
        ];
        $recapActiviy = [
            'Echauffement' => [],
            'Corps' => [],
            'Retour au calme' => [],
        ];
        $position = 1;
        foreach($exercises as $name => $descript) {
            $exercise = (new Exercise())
                ->setName($name)
                ->setUser($userAdmin)
                ->setPosition($position)
                ->setDescription($descript);
            $manager->persist($exercise);
        }
        $manager->flush();
        $activityEchauffOptions = [
            'Echauffement 1' => ['Meneur', 'Suiveur'],
            'Echauffement 2' => ['Cible Poing', 'Cible Poing'],
            'Echauffement 3' => ['Cible pied', 'Cible pied'],
            'Echauffement 4' => ['Cible pied/poings', 'Cible pied/poings'],
        ];

        $activityCorpsOptions = [
            'Corps 1' => ['Attaque 1', 'Riposte 1', 'instruction' => 'S\'appliqué sur la gestuelle,
                fouetté : parade, riposte :
                ATTENTE : je ne suis pas
                touché et je touche (situations
                simples) : (Tireur B n\'est
                touché et touche derrière)', 'objective' => 'situation stéréotypée.'],
            'Corps 2' => ['Attaque 2', 'Riposte 1', 'instruction' => 'S\'appliqué sur la gestuelle,
                fouetté : parade, riposte :
                ATTENTE : je ne suis pas
                touché et je touche (situations
                simples) : (Tireur B n\'est
                touché et touche derrière)', 'objective' => 'situation simple.'],
            'Corps 3' => ['Attaque 2', 'Riposte 2', 'instruction' => 'S\'appliqué sur la gestuelle,
                fouetté : parade, riposte :
                ATTENTE : je ne suis pas
                touché et je touche (situations
                simples) : (Tireur B n\'est
                touché et touche derrière)', 'objective' => 'situation intermédiaire.'],
            'Corps 4' => ['Attaque 3', 'Riposte 3', 'instruction' => 'S\'appliqué sur la gestuelle,
                    fouetté : parade, riposte :
                    ATTENTE : je ne suis pas
                    touché et je touche (situations
                    simples) : (Tireur B n\'est
                    touché et touche derrière)', 'objective' => 'situation stéréotypée.'],
            'Corps 5' => ['Attaque 4', 'Riposte 3', 'instruction' => 'S\'appliqué sur la gestuelle,
                    fouetté : parade, riposte :
                    ATTENTE : je ne suis pas
                    touché et je touche (situations
                    simples) : (Tireur B n\'est
                    touché et touche derrière)', 'objective' => 'situation simple.'],
            'Corps 6' => ['Attaque 4', 'Riposte 4', 'instruction' => 'S\'appliqué sur la gestuelle,
                    fouetté : parade, riposte :
                    ATTENTE : je ne suis pas
                    touché et je touche (situations
                    simples) : (Tireur B n\'est
                    touché et touche derrière)', 'objective' => 'situation intermédiaire.'],
            'Corps 7' => ['Attaque 5', 'Riposte 5', 'instruction' => 'S\'appliquer sur la gestuelle +
appliquer ce qui a été appris,
trajectoir rectiligne contre
trajectoir circulaire', 'objective' => 'situation aménagée.'],
            'Corps 8' => ['Libre', 'Libre', 'instruction' => 'S\'appliquer sur la gestuelle +
                appliquer ce qui a été appris,
                trajectoir rectiligne contre
                trajectoir circulaire', 'objective' => 'situation réelle.'],
        ];

         $activityRetourCalmOptions = [
            'Retour au calme' => ['Pour tous'],
        ];

        $position = 1;
        foreach($activityEchauffOptions as $name => $options) {
            $exr1 = $this->exerciseRepository->findOneBy(['name' => $options[0]]);
            $exer2 = $this->exerciseRepository->findOneBy(['name' => $options[1]]);
            //Echauffement
            $activity = (new Activity());
            $activity->setName($name);
            $activity->setUser($userAdmin);
            $activity->setDuration('2[2+1]');
            if ($exr1 !== null) {
                $exr1->setPosition(1);
                $manager->persist($exr1);
                $activity->addExercise($exr1);
            }
            if ($exer2 !== null) {
                $exer2->setPosition(1);
                $manager->persist($exer2);
                $activity->addExercise($exer2);
            }

            $manager->persist($activity);
            $recapActiviy['Echauffement'][] = $activity;
        }

        $position = 1;
         foreach($activityCorpsOptions as $name => $options) {
            $exr1 = $this->exerciseRepository->findOneBy(['name' => $options[0]]);
            $exer2 = $this->exerciseRepository->findOneBy(['name' => $options[1]]);
            //Echauffement
            $activity = (new Activity());
            $activity->setUser($userAdmin);
            $activity->setName($name)
                ->setObjective($options['objective'])
                ->setInstruction($options['instruction']);
            if ($exr1 !== null) {
                $exr1->setPosition(1);
                $manager->persist($exr1);
                $activity->addExercise($exr1);
            }
            if ($exer2 !== null) {
                $exer2->setPosition(1);
                $manager->persist($exer2);
                $activity->addExercise($exer2);
            }
            $activity->setDuration('2[2+1]');
            $manager->persist($activity);
            $recapActiviy['Corps'][] = $activity;
        }

        foreach($activityRetourCalmOptions as $name => $options) {
            $exr1 = $this->exerciseRepository->findOneBy(['name' => $options[0]]);
            //Echauffement
            $activity = (new Activity());
            $activity->setUser($userAdmin);
            $activity->setName($name);
            if ($exr1 !== null) {
                $exr1->setPosition(1);
                $manager->persist($exr1);
                $activity->addExercise($exr1);
            }
            $activity->setDuration('4[2+1]');
            $manager->persist($activity);
            $recapActiviy['Retour au calme'][] = $activity;
        }

        $sessionNames = [
            'Séance semaine 1' => ['Gant vert : Je ne suis pas touché et je touche,( situations simples), attaques varié 2 coups, risposet 2 coups', 'Gant vert']
        ];

        $blocNames = [
            'Echauffement' => ['Travail de cible, chacun son tour, priorité à la gestuelle et la technique', 'activity' => []],
            'Corps' => ['suivre le thème de la séance, effectuer des situations variée, priorité à la gestuelle, verifier les critères de réussites (touche, technique)', 'activity' => []],
            'Retour au calme' => ['Travail de renforcement musculaire, pompes, gainage, flexions et étirement','activity' => []],
        ];
        foreach($blocNames as $blocName => $options) {
            $block = (new Block())
            ->setUser($userAdmin)
                ->setName($blocName)
                ->setDescription($options[0])
                ->setDuration(30);
            /** @var Activity $activity */
            foreach($recapActiviy[$blocName] as $activity) {
                if ($activity !== null) {
                    $manager->persist($activity);
                    $block->addActivity($activity);
                }
            }
            $manager->persist($block);
            $blocs[] = $block;
        }


        foreach($sessionNames as $sessionName => $options) {
            $session = (new Session())
                ->setName($sessionName)
                ->setUser($userAdmin)
                ->setDescription($options[0])
                ->setDuration(90)
                ->setTarget($options[1]);
                $position = 1;
                /**@var Block $block */
                foreach($blocs as $block) {
                    if ($block !== null) {
                        $session->addBlock($block);
                    }
                }
            $manager->persist($session);
        }
        $manager->flush();
    }
}
