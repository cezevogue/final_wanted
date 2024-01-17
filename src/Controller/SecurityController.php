<?php

namespace App\Controller;

use App\Entity\OrderPurchase;
use App\Entity\User;
use App\Form\NewPasswordType;
use App\Form\RegisterType;
use App\Repository\OrderPurchaseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/security')]
class SecurityController extends AbstractController
{

    // méthode d'inscription
    #[Route('/register', name: 'register')]
    public function index(EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $hasher, MailerInterface $mailer): Response
    {

        // nouvel objet utilisateur
        $user = new User();
        // on défini son statut d'activation du compte à 0 par defaut
        $user->setActive(0);
        // on génère un token pour le transmettre dans le mail d'activation
        $user->setToken($this->generateToken());
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // on hash le mot de passe grace à la UserPasswordHasherInterface qui va vérifier que le user implement la userInterface ainsi que la PasswordAuthenticatedUserInterface (c'est elle qui va vérifier l'encodage dans le security.yml).

            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));


            $manager->persist($user);
            $manager->flush();
            $userMail = $user->getEmail();

            // préparation de l'envoie de l'email avec les infos de l'objet user en utilisant un template
            // les configuration du mailer sont posées dans le .env à MAILER_DNS (on passe par un compte brevo anciennement sendinBlue)
            $email = (new TemplatedEmail())
                ->from('cezdesaulle.evogue@gmail.com')
                ->to($userMail)
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Activer votre compte Wanted')
                ->htmlTemplate('email/validateAccount.html.twig')
                ->context([
                    'user' => $user,
                ]);
            // $mailer->IsSMTP();
            $mailer->send($email);
            // do anything else you need here, like send an email
            $this->addFlash('success', "Votre compte a bien été créé, allez vite l'activer");

            return $this->redirectToRoute('app_login');


        }


        return $this->render('security/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    // route appelée lors du click de confirmation de l'email d'activation du compte
    #[Route('/validate-account/{token}', name: 'validate_account')]
    public function validate_account($token, UserRepository $repo, EntityManagerInterface $manager)
    {
        // on recherche un user par son token receptionné en paramètre de l'url
        $user = $repo->findOneBy(['token' => $token]);
        if ($user) {
            // si on récupère un utilisateur
            // on passe sa clé active à 1
            // et on reset son token
            $user->setActive(1);
            $user->setToken(null);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', "Votre compte a bien été activé");
        } else {
            // sinon on renvoi un message d'erreur
            $this->addFlash('danger', "Une erreur s'est produite");
        }

        return $this->redirectToRoute('app_login');

    }


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // méthode pour généré la création d'un token unique
    private function generateToken()
    {
        // rtrim supprime les espaces en fin de chaine de caractère
        // strtr remplace des occurences dans une chaine ici +/ et -_ (caractères récurent dans l'encodage en base64) par des = pour générer des url valides
        // ce token sera utilisé dans les envoie de mail pour l'activation du compte ou la récupération de mot de passe
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    // fonction mot de passe oublié pour accéder
    // au formulaire de demande d'email et générer l'envoie à l'adresse mail saisie à la condition qu'un utilisateur ait un compte à cet email
    #[Route('/reset/password', name: "reset_password")]
    public function reset(Request $request, UserRepository $repo, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        // récupération de la saisie de l'email provenant du formulaire formulaire
        $email = $request->request->get('email', '');
        if (!empty($email)) {
       // si on a un email de renseigné
            // on requête un user grace à son email
            $user = $repo->findOneBy(['email' => $email]);


            if ($user != null && $user->isActive()) {
                // si il y a un user et que son compte est actif
                // on génère un token que l'on enregistre en BDD
                $user->setToken($this->generateToken());
                $entityManager->persist($user);
                $entityManager->flush();

                // on prépare l'email
                $email = (new TemplatedEmail())
                    ->from('cezdesaulle.evogue@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Reset mot de passe')
                    ->htmlTemplate('email/resetPassword.html.twig')
                    ->context([
                        'user' => $user,
                    ]);
                // on envoie
                $mailer->send($email);
                $this->addFlash('success', "un email de reset vous a été envoyé");

                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('error', "votre compte lié a ce mail n'est pas actif, activez le d'abord.");
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/forgotPassword.twig');
    }

    // route d'entrée au click du mail de réinitialisation
    #[Route('/new/password/{token}', name: "new_password")]
    public function newPassword($token, UserRepository $repo, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        // on récupère un user par son token
        $user = $repo->findOneBy(['token' => $token]);
        if ($user) {
            // si il y a on créé le formulaire de reset password
            $form = $this->createForm(NewPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // on hash le nouveau mdp
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                // on repasse le token à null
                $user->setToken(null);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', "Votre mot de passe a bien été modifié");
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/newPassword.html.twig', [
                'form' => $form
            ]);


        }

    }


    // profil de l'utilisateur
    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        // il manque ici le traitement du changement d'infos pour mail et nickname


        return $this->render('security/profile.html.twig', [

        ]);
    }

    // page renvoyant à l'utilisateur l'historique de ses commandes ainsi que leur statut de prise en charge
    #[Route('/order_purchases', name: 'order_purchases')]
    public function order_purchases(OrderPurchaseRepository $repository): Response
    {

        $orders=$repository->findBy(['user'=>$this->getUser()],['date'=>'DESC']);


        return $this->render('security/order_purchases.html.twig', [
        'orders'=>$orders
        ]);
    }

    // page de détail d'une commande
    #[Route('/order_detail/{id}', name: 'order_detail')]
    public function order_detail(OrderPurchase $order): Response
    {




        return $this->render('security/order_detail.html.twig', [
            'order'=>$order
        ]);
    }

}
