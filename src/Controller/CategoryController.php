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

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    #[Route('/update/{id}', name: 'category_update')]
    #[Route('/', name: 'category_create')]
    public function index(Request $request, EntityManagerInterface $manager,CategoryRepository $repository, $id=null): Response
    {

        $categories=$repository->findAll();

        if ($id){

            $category=$repository->find($id);

        }else{

            $category=new Category();
        }



        $form=$this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

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

    #[Route('/delete/{id}', name: 'category_delete')]
    public function delete_category(Category $category, EntityManagerInterface $manager): Response
    {
        $manager->remove($category);
        $manager->flush();
        $this->addFlash('info', 'Opération réalisée avec succès');

        return $this->redirectToRoute('category_create');
    }


}
