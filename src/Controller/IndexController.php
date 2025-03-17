<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/', name: 'article_show')]
    public function home(): Response
    {
        // Get all articles from the database
        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        
        return $this->render('articles/index.html.twig', [
            'articles' => $articles
        ]);
    }
    
    #[Route('/article/save')]
    public function save(): Response 
    {
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix('1000');
        
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        
        return new Response('Article enregistré avec id ' . $article->getId());
    }
    
    #[Route('/article/new', name: 'new_article', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new Article();
        
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', NumberType::class)
            ->add('save', SubmitType::class, [
                'label' => 'Créer'
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            
            $this->entityManager->persist($article);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('article_show');
        }
        
        return $this->render('articles/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]
public function edit(Request $request, int $id): Response
{
    $article = $this->entityManager->getRepository(Article::class)->find($id);
    
    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }
    
    $form = $this->createFormBuilder($article)
        ->add('nom', TextType::class)
        ->add('prix', NumberType::class)
        ->add('save', SubmitType::class, [
            'label' => 'Modifier'
        ])
        ->getForm();
        
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $this->entityManager->flush();
        
        return $this->redirectToRoute('article_show');
    }
    
    return $this->render('articles/edit.html.twig', [
        'form' => $form->createView()
    ]);
}
#[Route('/article/delete/{id}', name: 'delete_article')]
public function delete(int $id): Response
{
    $article = $this->entityManager->getRepository(Article::class)->find($id);
    
    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }
    
    $this->entityManager->remove($article);
    $this->entityManager->flush();
    
    return $this->redirectToRoute('article_show');
}
}