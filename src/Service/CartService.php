<?php

namespace App\Service;

use App\Repository\ActivityRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{

    private $repository;

    private $session;

    // on créé ce constructeur pour injecter dans nos controller le repository et la session à l'appel du service
    public function __construct(ActivityRepository $activityRepository, RequestStack $requestStack)
    {
        $this->repository=$activityRepository;
        $this->session=$requestStack;

    }


    // pour ajouter au panier
    public function add($id)
    {
        // on récupère toute la session
        $local=$this->session->getSession();
        $cart=$local->get('cart', []);
        // on verifie que l'activité n'est pas été déjà ajouté en session pour eviter les doublons
        if (!isset($cart[$id])){

            $cart[$id]=1;
        }
        // dans le cas ou l'on voudrait ajouter plusieurs fois au panier (ex: site ecommerce)
//        else
//        {
//           // ici on incrémenterai la quantité
//            $cart[$id]++;
//        }

        // on met à jour la session après avoir travaillé dessus
        $local->set('cart', $cart);

    }

    // pour ajouter au panier
    public function remove($id)
    {
        // on récupère toute la session
        $local=$this->session->getSession();
        $cart=$local->get('cart', []);
        // on verifie l'existence de cette entrée et la quantité de fois ajouté
        // si egale à 1 on supprime totalement cette entrée en session
        if (isset($cart[$id]) && $cart[$id]==1){

            unset($cart[$id]);
        }
        //sinon on décrémente la quantité
//         if (isset($cart[$id]) && $cart[$id]>1)
//        {
//           // ici on décrémenterai la quantité
//            $cart[$id]--;
//        }

        // on met à jour la session après avoir travaillé dessus

        $local->set('cart', $cart);
    }

    public function destroy()
    {
        $local=$this->session->getSession();
        // on détruit la session cart intégralement
        $local->remove('cart');


    }

    public function getCartWithData()
    {
        $local=$this->session->getSession();
        $cart=$local->get('cart', []);

        // on initialise un tableau vide pour le charger du détail pour chaques id
        // présent en session ainsi que la quantité de fois qu'il a été ajouté au panier
        $cartWithData=[];
        foreach ($cart as $id=>$quantity)
        {
            $cartWithData[]=[
                // méthode retournant une seule entrée dans la table activity grace à son id
                'activity'=>$this->repository->find($id),
                'quantity'=>$quantity

            ];

        }
        return $cartWithData;




    }










}