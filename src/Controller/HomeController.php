<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Rating;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\TagRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class HomeController extends AbstractController
{

    // page d'accueil
    #[Route('/', name: 'home')]
    #[Route('/filter', name: 'filter')]
    public function home(TagRepository $tagRepository,ProductRepository $repository, CategoryRepository $categoryRepository,Request $request): Response
    {




        // renvoie la liste des products avec la possibilité de filtrer, de voir le détail et de l'ajouter au panier

        // récupération de tout les products
        $products = $repository->findAll();

        $categories = $categoryRepository->findAll();




      if (!empty($_POST)){

          if ($request->request->get('categorie') && empty($request->request->get('prix'))):
              $products = $repository->findBy(['category' => $request->request->get('categorie')]);
          // dd($products);
          elseif ($request->request->get('prix') && empty($request->request->get('categorie'))):
              $products = $repository->findByPrice($request->request->get('prix'));
          elseif ($request->request->get('prix') && $request->request->get('categorie')):
              $products = $repository->findByPriceCategory($request->request->get('prix'), $request->request->get('categorie'));

          else:
              $products = $repository->findAll();
          endif;

      }



        return $this->render('home/home.html.twig', [
            "categories" => $categories,
            'products' => $products
        ]);
    }




    // page détail d'un product coté utilisateur
    #[Route('/oneProduct/{id}', name: 'oneProduct')]
    public function oneProduct(Product $product): Response
    {

        // ici $product est rempli de toutes ses information car id en param et entite en injection de dépendance (à préciser que cela ne fonctionne pas si plusieurs entité ont été injecté ou qu'un repository a été injecté dans les ())


        return $this->render('home/oneProduct.html.twig', [
            'product' => $product
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

    // retrait complet du product du panier
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
            'cart' => $cart,
            'total' => $cartService->getTotal()
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/rate/{id}', name: 'rate')]
    public function rate(Product $product, Request $request, EntityManagerInterface $manager)
    {

        if (!empty($_POST)) {
            $comment = new Rating();
            $comment->setRate($request->request->get('rate'));
            $comment->setComment($request->request->get('comment'));
            $comment->setProduct($product);
            $comment->setPublish(false);
            $comment->setPublishDate(new \DateTime());
            $comment->setUser($this->getUser());
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash('success', 'Merci pour votre  contribution');
            return $this->redirectToRoute('oneProduct', ['id' => $product->getId()]);

        }


    }

    #[Route('/comments/{id}', name: 'comments')]
    public function comments(Product $product, Request $request, EntityManagerInterface $manager)
    {


        $comments = $product->getRatings();


        return $this->render('home/comments.html.twig', [
            'comments' => $comments
        ]);
    }


    #[Route('/search', name: 'app_search', methods: 'GET')]
    public function searchAction(Request $request, ProductRepository $productRepository,CategoryRepository $categoryRepository,TagRepository $tagRepository)
    {


        $requestString = $request->get('q');

        $productsFromProduct = $productRepository->findBySearch($requestString);
        $productsFromTag = $tagRepository->findBySearch($requestString);
        $productsFromCategory = $categoryRepository->findBySearch($requestString);
        $result['search']=$requestString;
        if (!$productsFromProduct && !$productsFromCategory && !$productsFromTag) {
            $result['entities']['error'] = "Aucun résultat";
        } else {
           if ($productsFromProduct){
               $result['entities']['Produits'] = $this->getRealEntities($productsFromProduct);
               $result['entities']['Produits']['count']=count($productsFromProduct);

           }
           if ($productsFromTag )
           {
               $result['entities']['Tags'] = $this->getRealEntities($productsFromTag);
               $result['entities']['Tags']['count']=0;
               foreach ($productsFromTag as $tag){

                   $products=$tag->getProducts();
                   $result['entities']['Tags']['count']+=count($products);

               }

              // $result['entities']['Tags']['count']=10;

           }

           if ($productsFromCategory)
           {
               $result['entities']['Catégorie'] = $this->getRealEntities($productsFromCategory);
               $products=$productRepository->findBy(['category'=>$productsFromCategory]);
               $result['entities']['Catégorie']['count']=count($products);

           }



        }

        return new Response(json_encode($result));
    }

    public function getRealEntities($entities): array
    {
        $realEntities=[];
        foreach ($entities as $entity) {
            $realEntities[$entity->getId()] = $entity->getTitle();
        }

        return $realEntities;
    }


    #[Route('/search/{entity}/{id}', name: 'final_search')]
    public function finalSearch(Request $request, ProductRepository $productRepository,CategoryRepository $categoryRepository,TagRepository $tagRepository, $entity, $id)
    {
        if ($entity=='Produits'){


          if (!is_numeric($id)){

              $products=$productRepository->findBySearch($id);
          }else{
              $products=$productRepository->findBy(['id'=>$id]);

          }

           $count= count($products);
        }

        if ($entity=='Catégorie'){
            $category=$categoryRepository->find($id);
            $products=$productRepository->findBy(['category'=>$category]);
            $count= count($products);
        }

        if ($entity=='Tags'){
            $tag=$tagRepository->find($id);
            $products=$tag->getProducts();
            $count= count($products);
        }


        return $this->render('home/display_products.html.twig', [
            'products'=>$products,
            'count'=>$count,
            'type'=>$entity
        ]);

    }




}
