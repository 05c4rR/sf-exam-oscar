<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product/crud')]
class ProductCrudController extends AbstractController
{
    #[Route('/', name: 'app_product_crud_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product_crud/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_crud_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $images */
            $images = $form->get('images')->getData();
            $stockQuantity = $form->get('stockQuantity')->getData();

            $stock = new Stock();
            $stock->setQuantity($stockQuantity);
            $entityManager->persist($stock);

            $product->setStock($stock);

            if ($images) {
                foreach ($images as $image) {
                    if ($image) {
                        $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
    
                        try {
                            $image->move(
                                $this->getParameter('images_directory'), // Défini dans services
                                $newFilename
                            );
    
                            $img = new Image();
                            $img->setImageName($newFilename);
                            $img->setProduct($product);
                            $entityManager->persist($img);
    
                        } catch (FileException $e) {
                            $form->addError(new FormError("Erreur lors de l'upload de l'image"));
                        }
                    }
                }
            }
    
            $entityManager->persist($product);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_product_crud_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('product_crud/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_crud_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product_crud/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $images */
            $images = $form->get('images')->getData();
            $stockQuantity = $form->get('stockQuantity')->getData();

            $stock = $product->getStock() ?: new Stock();
            $stock->setQuantity($stockQuantity);
            $stock->setProduct($product);

            $entityManager->persist($stock);
            $entityManager->persist($product);

            if ($images) {
                foreach ($images as $image) {
                    if ($image) {
                        $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
            
                        try {
                            $image->move(
                                $this->getParameter('images_directory'),  // Défini dans services.yaml
                                $newFilename
                            );
    
                            $img = new Image();
                            $img->setImageName($newFilename);
                            $img->setProduct($product);
                            $entityManager->persist($img);
    
                        } catch (FileException $e) {
                            $form->addError(new FormError("Erreur lors de l'upload de l'image"));
                        }
                    }
                }
            }

            $entityManager->flush();
    
            return $this->redirectToRoute('app_product_crud_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('product_crud/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'app_product_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
