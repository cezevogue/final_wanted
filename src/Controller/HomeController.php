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
    #[Route('/', name: 'home')]
    public function home(ProductRepository $productRepository): Response
    {

        $products=$productRepository->findAll();


        return $this->render('home/home.html.twig', [
           'products'=>$products
        ]);
    }

    #[Route('/oneProduct/{id}', name: 'oneProduct')]
    public function oneProduct(Product $product): Response
    {




        return $this->render('home/oneProduct.html.twig', [
            'product'=>$product
        ]);
    }

    #[Route('/cart/add/{id}/{param}', name: 'cart_add')]
    public function cart_add(CartService $cartService, $id, $param): Response
    {
        $cartService->add($id);
        $this->addFlash('info', 'Ajouté en favori');
        return $this->redirectToRoute($param);
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function cart_remove(CartService $cartService, $id): Response
    {
        $cartService->remove($id);
        $this->addFlash('info', 'Retiré des favoris');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function cart_delete(CartService $cartService, $id): Response
    {
        $cartService->remove($id);
        $this->addFlash('info', 'Retiré des favoris');
        return $this->redirectToRoute('cart');
    }

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
