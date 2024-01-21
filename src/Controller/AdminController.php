<?php

namespace App\Controller;

use App\Repository\OrderPurchaseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // route pour afficher le dashboard, généralement page de statistique
    // provenant de données suite à l'inscription sur AT Analytics (remplacant de google analytic)
    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'title'=>'Dashboard'
        ]);
    }

    // page de gestion des utilisateurs
    // permet de modifier les roles utilisateurs ainsi que de désactiver leur compte
    #[Route('/users', name: 'users')]
    #[Route('/users/{id}/{role}', name: 'user_update')]
    #[Route('/users/{id}', name: 'user_delete')]
    public function users(UserRepository $repository, EntityManagerInterface $manager, $id = null, $role = null): Response
    {
        $users = $repository->findAll();
        if ($id) {
            $user = $repository->find($id);
            if ($role) {
                // ici pour modifier le role
                $user->setRoles([$role]);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Rôle modifié');

            } else {
             // ici désactivation du compte
                // utile pour bannir momentanément un utilisateur,
                //mais necessite l'envoie d'un email explicatif avec un lien pour communiquer avec l'administrateur du site
                $user->setActive(0);
                $this->addFlash('success', 'Compte désactivé');
            }

            return $this->redirectToRoute('users');


        }

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'title'=>'Gestion des utilisateurs'
        ]);
    }


    // route de suivi des commandes
    // permet de suivre le cours des commandes et d'en
    // modifier l'état
    #[Route('/orders', name: 'orders')]
    #[Route('/order/status/{id}/{status}', name: 'order_status_upgrade')]
    public function orders(OrderPurchaseRepository $repository, EntityManagerInterface $manager, $id=null, $status=null): Response
    {
        if ($id && $status) {
            // modification du statut transmis en paramètre
            // pour la commande d'id transmis en paramètre
            $order = $repository->find($id);
            $order->setStatus($status);
            $manager->persist($order);
            $manager->flush();
            $this->addFlash('success', 'Statut à jour');
            return $this->redirectToRoute('orders');

        }

// récupérationde toutes les commandes par date desc
        $orders = $repository->findBy([], ['date' => 'DESC', 'status' => 'ASC']);

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
            'title'=>'Gestion des commandes'
        ]);
    }


}
