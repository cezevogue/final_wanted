<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Product;
use App\Form\MediaType;
use App\Form\ProductType;
use App\Repository\MediaRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/create', name: 'product_create')]
    #[Route('/update/infos/{id}', name: 'product_update_infos')]
    public function product_create(EntityManagerInterface $manager, Request $request,ProductRepository $repository, $id): Response
    {
        if (!$id){
            $product = new Product();

        }else{
            $product=$repository->find($id);

        }


        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($product);
            $manager->flush();



            if (!$id){
                $this->addFlash('info', 'produit ajouté, ajoutez des médias en lien');
                return $this->redirectToRoute('media_create', ['id' => $product->getId()]);
            }else{
                $this->addFlash('info', 'informations modifiées');
                return $this->redirectToRoute('product_details', ['id' => $product->getId()]);

            }



        }


        return $this->render('product/product_create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/media/create/{id}', name: 'media_create')]
    public function media_create(Request $request, EntityManagerInterface $manager, ProductRepository $repository, $id): Response
    {

        $media = new Media();


        $form = $this->createForm(MediaType::class, $media);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = $repository->find($id);
            // on recupère le fichier uploader
            $file = $form->get('src')->getData();

            $number=count($product->getMedias()) +1;

            // on renomme le fichier en le concaténant avec date complète puis nom d'origine du fichier
            $file_name = date('Y-m-d-H-i-s') . '-' . $product->getTitle().$number.'.'. $file->getClientOriginalExtension();

            // on upload
            $file->move($this->getParameter('upload_dir'), $file_name);

            // on reaffecte le renommage à l'objet
            $media->setSrc($file_name);
            $media->setTitle($product->getTitle().$number);
            $product->addMedia($media);
            $manager->persist($media);

            $manager->persist($product);
            $manager->flush();
            $this->addFlash('success', 'Média créé, vous pouvez en ajouter un autre ou cliquer sur terminé');

            return $this->redirectToRoute('media_create', ['id' => $product->getId()]);

        }


        return $this->render('product/media_create.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajout de média'
        ]);
    }

    #[Route('/list', name: 'product_list')]
    public function product_list(ProductRepository $repository): Response
    {

        $products=$repository->findAll();
        return $this->render('product/product_list.html.twig', [
            'products'=>$products
        ]);
    }

    #[Route('/update/medias/{id}', name: 'product_update_medias')]
    public function product_update_medias(Product $product): Response
    {


        return $this->render('product/product_update_medias.html.twig', [
            'id' => $product->getId(),
            'medias'=>$product->getMedias()
            ]);
    }

    #[Route('/delete/medias', name: 'product_delete_medias')]
    public function product_delete_medias(Request $request,MediaRepository $repository, EntityManagerInterface $manager): Response
    {

        $medias_id = $request->request->all()['choice'];
        $product_id='';
        foreach ($medias_id as $id) {

            $media = $repository->find($id);
            $product_id=$media->getProduct()->getId();


                unlink($this->getParameter('upload_dir').'/'.$media->getSrc());



            $manager->remove($media);

        }
        $manager->flush();
        $this->addFlash('info', 'Opération réalisée avec succès');



        return $this->redirectToRoute('product_details', ['id' => $product_id]);

    }

    #[Route('/details/{id}', name: 'product_details')]
    public function product_details(Product $product): Response
    {


        return $this->render('product/product_details.html.twig', [
        'product'=>$product
        ]);
    }


    #[Route('/delete/{id}', name: 'product_delete')]
    public function product_delete(Product $product, EntityManagerInterface $manager): Response
    {
        $medias=$product->getMedias();

        foreach ($medias as $media)
        {

            unlink($this->getParameter('upload_dir').'/'.$media->getSrc());

        }



        $manager->remove($product);
        $manager->flush();

        $this->addFlash('info', 'Opération réalisée avec succès');
        return $this->redirectToRoute('product_list');
    }



}
