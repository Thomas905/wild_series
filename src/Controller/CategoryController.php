<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\Program;

/**
 * @Route("/category", name="category_")
 */

class CategoryController extends AbstractController
{

    /**
     * @Route("/", "name=index")
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()
             ->getRepository(Category::class)
             ->findAll();

        return $this->render('category/index.html.twig', [
        'categories' => $categories,
        ]);
    }


    /**
     * @Route("/category/{categoryName}", name="show")
     */
    public function show(string $categoryName): Response
    {
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->findOneBy(['name' => $categoryName]);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category with name : '.$categoryName.' found in category\'s table.'
            );
        }

        $programs = $this->getDoctrine()
        ->getRepository(Program::class)
        ->findBy(
            ['category' => $category->getId()],
            ['id' => 'DESC'], 3,
        );

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'programs' => $programs
        ]);

    }
}
