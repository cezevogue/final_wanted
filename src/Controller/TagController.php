<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tag')]
#[IsGranted('ROLE_ADMIN')]
class TagController extends AbstractController
{
    // similaire au catégorie controller


    #[Route('/update/{id}', name: 'tag_update')]
    #[Route('/', name:'tag_create')]
    public function index(Request $request, EntityManagerInterface $manager,TagRepository $repository, $id=null): Response
    {

        $tags=$repository->findAll();

        if ($id){

            $tag=$repository->find($id);

        }else{

            $tag=new Tag();
        }



        $form=$this->createForm(TagType::class, $tag);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            $manager->persist($tag);
            $manager->flush();
            $this->addFlash('info', 'Opération réalisée avec succès');
            return $this->redirectToRoute('tag_create');




        }


        return $this->render('tag/index.html.twig', [
            'formu'=>$form->createView(),
            'tags'=>$tags,
            'title'=>'Gestion des tags'
        ]);

    }

    #[Route('/delete/{id}', name: 'tag_delete')]
    public function delete_tag(Tag $tag, EntityManagerInterface $manager): Response
    {
        $manager->remove($tag);
        $manager->flush();
        $this->addFlash('info', 'Opération réalisée avec succès');

        return $this->redirectToRoute('tag_create');
    }

}
