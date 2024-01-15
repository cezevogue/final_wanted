<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\NewPasswordType;
use App\Form\RegisterType;
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
    #[Route('/register', name: 'register')]
    public function index(EntityManagerInterface $manager, Request $request,UserPasswordHasherInterface $hasher, MailerInterface $mailer): Response
    {

        $user=new User();
        $user->setActive(0);
        $user->setToken($this->generateToken());
        $form=$this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setPassword($hasher->hashPassword($user,$user->getPassword()));


            $manager->persist($user);
            $manager->flush();
            $userMail = $user->getEmail();
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
           'form'=>$form->createView()
        ]);
    }


    #[Route('/validate-account/{token}', name:'validate_account')]
    public function validate_account($token, UserRepository $repo, EntityManagerInterface $manager)
    {
        $user = $repo->findOneBy(['token' => $token]);
        if($user)
        {
            $user->setActive(1);
            $user->setToken(null);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', "Votre compte a bien été activé");
        }
        else {
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

    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    #[Route('/reset/password', name:"reset_password")]
    public function reset(Request $request, UserRepository $repo, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $email = $request->request->get('email', '');
        if(!empty($email)){

            $user = $repo->findOneBy(['email' => $email]);

            if($user != null && $user->isActive())
            {
                $user->setToken($this->generateToken());
                $entityManager->persist($user);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('cezdesaulle.evogue@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Reset mot de passe')
                    ->htmlTemplate('email/resetPassword.html.twig')
                    ->context([
                        'user' => $user,
                    ]);
                $mailer->send($email);
                $this->addFlash('success', "un email de reset vous a été envoyé");

                return $this->redirectToRoute('home');
            }
            else {
                $this->addFlash('error', "votre compte lié a ce mail n'est pas actif, activez le d'abord.");
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/forgotPassword.twig');
    }
    #[Route('/new/password/{token}', name:"new_password")]
    public function newPassword($token, UserRepository $repo, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $repo->findOneBy(['token'=>$token]);
        if($user)
        {
            $form = $this->createForm(NewPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
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









}
