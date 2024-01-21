<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    // création, lecture et modification catégorie
    #[Route('/update/{id}', name: 'category_update')]
    #[Route('/', name: 'category_create')]
    public function index(Request $request, EntityManagerInterface $manager,CategoryRepository $repository, $id=null): Response
    {

        $categories=$repository->findAll();

        if ($id){
      // alors on est en modification et on va requêter la catégorie
            // en question grace à la méthode find qui prend l'id en paramètre
            $category=$repository->find($id);

        }else{
    // sinon on instancie une nouvelle catégorie
            $category=new Category();
        }


      // création de l'objet de formulaire en le reliant à son formulaire (le Type et l'objet à remplir)
        $form=$this->createForm(CategoryType::class, $category);

        // hydratation de l'objet grace à handleRequest
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            // on prépare et on execute avec le manager
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('info', 'Opération réalisée avec succès');
            return $this->redirectToRoute('category_create');




        }


        return $this->render('category/index.html.twig', [
            'formu'=>$form->createView(),
            'categories'=>$categories,
            'title'=>'Gestion des catégories'
        ]);

    }

    // suppression de catégorie
    #[Route('/delete/{id}', name: 'category_delete')]
    public function delete_category(Category $category, EntityManagerInterface $manager): Response
    {
        // on appel la méthode remove() et on execute
        $manager->remove($category);
        $manager->flush();
        $this->addFlash('info', 'Opération réalisée avec succès');

        return $this->redirectToRoute('category_create');
    }


}
