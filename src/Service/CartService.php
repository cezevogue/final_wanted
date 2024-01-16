<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{

    private $repository;

    private $session;

    // on créé ce constructeur pour injecter dans nos controller le repository et la session à l'appel du service
    public function __construct(ProductRepository $repository, RequestStack $requestStack)
    {
        $this->repository=$repository;
        $this->session=$requestStack;

    }


    // pour ajouter au panier
    public function add($id):void
    {
        // on récupère toute la session
        $local=$this->session->getSession();
        $cart=$local->get('cart', []);
        // on verifie que le produit n'a pas été déjà ajouté en session pour eviter les doublons
        if (!isset($cart[$id])){

            $cart[$id]=1;
        }
        // dans le cas ou l'on voudrait ajouter plusieurs fois au panier (ex: site ecommerce)
        else
        {
           // ici on incrémente la quantité
            $cart[$id]++;
        }

        // on met à jour la session après avoir travaillé dessus
        $local->set('cart', $cart);

    }





    // pour ajouter au panier
    public function remove($id):void
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
         if (isset($cart[$id]) && $cart[$id]>1)
        {
           // ici on décrémenterai la quantité
            $cart[$id]--;
        }

        // on met à jour la session après avoir travaillé dessus

        $local->set('cart', $cart);
    }


    public function delete($id):void
    {
        // on récupère toute la session
        $local=$this->session->getSession();
        $cart=$local->get('cart', []);
        // on verifie l'existence de cette entrée et la quantité de fois ajouté
        // si egale à 1 on supprime totalement cette entrée en session
        if (isset($cart[$id]) && $cart[$id]==1){

            unset($cart[$id]);
        }
        $local->set('cart', $cart);
    }



    public function destroy():void
    {
        $local=$this->session->getSession();
        // on détruit la session cart intégralement
        $local->remove('cart');


    }

    public function getCartWithData(): array
    {
        $local=$this->session->getSession();
        $cart=$local->get('cart', []);

        // on initialise un tableau vide pour le charger du détail pour chaques id
        // présent en session ainsi que la quantité de fois qu'il a été ajouté au panier
        $cartWithData=[];
        foreach ($cart as $id=>$quantity)
        {
            $cartWithData[]=[
                // méthode retournant une seule entrée dans la table product grace à son id
                'product'=>$this->repository->find($id),
                'quantity'=>$quantity

            ];

        }
        return $cartWithData;


    }

    public function getTotal(): float
    {
        $total=0;
        foreach ($this->getCartWithData() as $data)
        {
            $total+=$data['product']->getPrice()*$data['quantity'];


        }
        return $total;
    }










}