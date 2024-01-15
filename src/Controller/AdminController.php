<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [

        ]);
    }

    #[Route('/users', name: 'users')]
    #[Route('/users/{id}/{role}', name: 'user_update')]
    #[Route('/users/{id}', name: 'user_delete')]
    public function users(UserRepository $repository,EntityManagerInterface $manager, $id=null, $role=null): Response
    {
        $users=$repository->findAll();
       if ($id)
       {
           $user=$repository->find($id);
           if ($role){
               $user->setRoles([$role]);
               $manager->persist($user);
               $manager->flush();
               $this->addFlash('success', 'Rôle modifié');

           }else{

               $user->setActive(0);
               $this->addFlash('success', 'Compte désactivé');
           }

           return $this->redirectToRoute('users');


       }

        return $this->render('admin/users.html.twig', [
        'users'=>$users
        ]);
    }


}
