<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    // page d'accueil
    #[Route('/', name: 'home')]
    public function home(ProductRepository $productRepository): Response
    {
       // renvoie la liste des produits avec la possibilité de filtrer, de voir le détail et de l'ajouter au panier

        // récupération de tout les produits
        $products=$productRepository->findAll();


        return $this->render('home/home.html.twig', [
           'products'=>$products
        ]);
    }

    // page détail d'un produit coté utilisateur
    #[Route('/oneProduct/{id}', name: 'oneProduct')]
    public function oneProduct(Product $product): Response
    {

        // ici $product est rempli de toutes ses information car id en param et entite en injection de dépendance (à préciser que cela ne fonctionne pas si plusieurs entité ont été injecté ou qu'un repository a été injecté dans les ())



        return $this->render('home/oneProduct.html.twig', [
            'product'=>$product
        ]);
    }

    // ajout au panier, param a pour but de définir la redirection en fonction de l'ajout via la page d'accueil ou via le panier
    #[Route('/cart/add/{id}/{param}', name: 'cart_add')]
    public function cart_add(CartService $cartService, $id, $param): Response
    {
        $cartService->add($id);
        $this->addFlash('info', 'Ajout au panier');
        return $this->redirectToRoute($param);
    }

    // retrait en quantité du panier
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function cart_remove(CartService $cartService, $id): Response
    {
        $cartService->remove($id);
        $this->addFlash('info', 'Retiré du panier ');
        return $this->redirectToRoute('cart');
    }

    // retrait complet du produit du panier
    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function cart_delete(CartService $cartService, $id): Response
    {
        $cartService->remove($id);
        $this->addFlash('info', 'Retiré du panier ');
        return $this->redirectToRoute('cart');
    }

    // page de panier et finalisation de commande avec stripe implémenté
    #[Route('/cart', name: 'cart')]
    public function cart(CartService $cartService): Response
    {
        $cart = $cartService->getCartWithData();


        return $this->render('home/cart.html.twig', [
            'cart'=>$cart,
            'total'=>$cartService->getTotal()
        ]);
    }


}
