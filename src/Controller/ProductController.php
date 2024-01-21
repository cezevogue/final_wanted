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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    // page de création et modification des produits uniquement sur les informations et non sur les médias
    #[Route('/create', name: 'product_create')]
    #[Route('/update/infos/{id}', name: 'product_update_infos')]
    public function product_create(EntityManagerInterface $manager, Request $request, ProductRepository $repository, $id = null): Response
    {
        if (!$id) {
            $product = new Product();
            $title = "Ajoute un produit";
        } else {
            $product = $repository->find($id);
            $title = "Modification produit";
        }


        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($product);
            $manager->flush();


            if (!$id) {
                $this->addFlash('info', 'produit ajouté, ajoutez des médias en lien');
                // en création on redirige sur la page d'ajout de médias en liens avec le produit d'où le param d'id du produit passé lors de la redirection
                return $this->redirectToRoute('media_create', ['id' => $product->getId()]);
            } else {
                // en modification on redirige sur la page de détail du produit
                $this->addFlash('info', 'informations modifiées');
                return $this->redirectToRoute('product_details', ['id' => $product->getId()]);

            }


        }


        return $this->render('product/product_create.html.twig', [
            'form' => $form->createView(),
            'title' => $title
        ]);
    }

    // Page de mise en liens des médias (création de médias) avec le produit
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
           // nombre de médias existant en lien avec le produit que l'on incrémente de 1 pour concaténé au title du média ainsi qu'au renommage du src ci-dessous
            $number = count($product->getMedias()) + 1;

            // on renomme le fichier en le concaténant avec date complète, son numéro puis nom d'origine du fichier (le title qui est celui du produit) et enfin son extension
            $file_name = date('Y-m-d-H-i-s') . '-' . $product->getTitle() . $number . '.' . $file->getClientOriginalExtension();

            // on upload en ayant préalablement configuré le parameter 'upload_dir' dans le services.yaml de Config
            //upload_dir: '%kernel.project_dir%/public/upload'
            $file->move($this->getParameter('upload_dir'), $file_name);

            // on reaffecte le renommage à l'objet
            $media->setSrc($file_name);
            $media->setTitle($product->getTitle() . $number);
            // on ajoute au produit le media
            $product->addMedia($media);
            // on persist le media
            $manager->persist($media);
            // on persist le produit
            $manager->persist($product);
            // on execute
            $manager->flush();
            $this->addFlash('success', 'Média créé, vous pouvez en ajouter un autre et valider ou cliquer sur terminé pour voir le détail');

            return $this->redirectToRoute('media_create', ['id' => $product->getId()]);

        }


        return $this->render('product/media_create.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajout de média'
        ]);
    }

    // page d'affichage de la liste des produits
    #[Route('/list', name: 'product_list')]
    public function product_list(ProductRepository $repository): Response
    {

        $products = $repository->findAll();
        return $this->render('product/product_list.html.twig', [
            'products' => $products,
            'title' => 'Gestion des produits'
        ]);
    }

    // page d'ajout de nouveaux médias au produit accessible par modifier les media et ajouter un nouveau média
    #[Route('/update/medias/{id}', name: 'product_update_medias')]
    public function product_update_medias(Product $product): Response
    {


        return $this->render('product/product_update_medias.html.twig', [
            'id' => $product->getId(),
            'medias' => $product->getMedias(),
            'title' => 'Gestion Médias du produit: ' . $product->getTitle()
        ]);
    }

    // suppression des médias par la page de gestion des médias
    #[Route('/delete/medias', name: 'product_delete_medias')]
    public function product_delete_medias(Request $request, MediaRepository $repository, EntityManagerInterface $manager): Response
    {
       // les checkbox de name choice[] sont récupéré du formulaire
        // car on peut vouloir supprimer plusieurs média.
        // on les récupère via notre formulaire en post avec
        // $request->request
        $medias_id = $request->request->all()['choice'];
        $product_id = '';
        foreach ($medias_id as $id) {
      // on boucle sur tout les id receptionné
            // pour chaque tour on récupère le média grace au repository et la méthode find
            $media = $repository->find($id);
            $product_id = $media->getProduct()->getId();

              // on supprime du dossier d'upload le fichier
            unlink($this->getParameter('upload_dir') . '/' . $media->getSrc());

           // puis on le supprime de la bdd
            $manager->remove($media);

        }
        // on execute
        $manager->flush();
        $this->addFlash('info', 'Opération réalisée avec succès');


        return $this->redirectToRoute('product_details', ['id' => $product_id]);

    }

    // page de détail du produit
    #[Route('/details/{id}', name: 'product_details')]
    public function product_details(Product $product): Response
    {


        return $this->render('product/product_details.html.twig', [
            'product' => $product,
            'title' => 'Détails produit'
        ]);
    }


    // suppression d'un produit
    #[Route('/delete/{id}', name: 'product_delete')]
    public function product_delete(Product $product, EntityManagerInterface $manager): Response
    {
        // on récupère tout les médias du produit
        $medias = $product->getMedias();
       // on supprime tout les médias du dossier d'upload
        foreach ($medias as $media) {

            unlink($this->getParameter('upload_dir') . '/' . $media->getSrc());

        }

     // puis on supprime en bdd le produit qui supprimera tout les médias en liens de même
        $manager->remove($product);
        $manager->flush();

        $this->addFlash('info', 'Opération réalisée avec succès');
        return $this->redirectToRoute('product_list');
    }


}
