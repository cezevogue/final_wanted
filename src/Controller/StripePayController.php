<?php

namespace App\Controller;

use App\Service\CartService;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripePayController extends AbstractController
{
    #[Route('/stripe/pay', name: 'app_stripe_pay')]
    public function index(CartService $cs): Response
    {

        $fullCart=$cs->getCartWithData();

        $line_items=[];


         foreach ($fullCart as $item)
          {
            $line_items[]=[
                'price_data'=>[
                    'unit_amount'=>$item['product']->getPrice()*100, 'currency'=>'EUR',
                    'product_data'=>['name'=>$item['product']->getTitle()
                    ]
                ],
                   'quantity'=>$item['quantity']


            ];
          }


         Stripe::setApiKey('sk_test_51NwOTlJ2Zd0dLxMHVyJSgNydBGye36Q96U200SAMIOxPBtTlK3UopVhKAsKHJrNFovwXw6Za2Qb4b4zq9x5NymCt00mw0P8R3y');
             $session=Session::create([
//                 'success_url'=>'https://www.lock.cezdigit.com/commande/success',
//                 'cancel_url'=>'https://www.lock.cezdigit.com/wishList',
                 'success_url'=>'http://127.0.0.1:8000/commande/success',
                 'cancel_url'=>'http://127.0.0.1:8000/cart',
                 'payment_method_types'=>['card'],
                 'line_items'=>$line_items,
                 'mode'=>'payment'
             ]);

             return $this->redirect($session->url, 303);



    }



      #[Route('/commande/{success}', name: 'commande')]
          public function commande(CartService $cartService,$success=null): Response
          {
             if ($success){

                 $this->addFlash('success', 'Merci pour votre confiance');
                 $cartService->destroy();
                 return $this->redirectToRoute('home');

             }else{
                 $this->addFlash('danger', 'Un problème est survenu merci de réitérer votre paiement');
                 return $this->redirectToRoute('home');

             }


          }






}
