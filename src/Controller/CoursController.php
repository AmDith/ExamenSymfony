<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CoursController extends AbstractController
{
    #[Route('/cours', name: 'cours.index')]
    public function index(CoursRepository $coursRepository, Request $request): Response
    {
        $formSearch=$this->createForm(CoursType::class);
        $formSearch->handleRequest($request);
        if ($formSearch->isSubmitted($request) && $formSearch->isValid()) {
            //c'est ici qu'on filtre par téléphone
            $cours = $coursRepository->findBy(['prof' => $formSearch->get('prof')->getData()]);
        }else{
            $clients = $clientRepository->paginateClients($page,$limit);
           
        }
        return $this->render('cours/index.html.twig', [
            'cours' => $cours,
            'formSearch' => $formSearch->createView(),
        ]);
    }

    // {
    //     /*
    //         Les methodes de repository permet de récupérer les données d'une entité
    //         findALL():retourne tous les objets de la classe
    //         find($id):retourne un objet en fonction de son id
    //         findOneBy(['field' =>'valeur']):retourne un objet en fonction de ses attributs


    //     */
        // $formSearch=$this->createForm(SearchClientType::class);
        // $formSearch->handleRequest($request);
        // $page =  $request->query->getInt('page',1);
        // $count = 0;
        // $maxPage =  0;
        // $limit = 8;
        // if ($formSearch->isSubmitted($request) && $formSearch->isValid()) {
        //     //c'est ici qu'on filtre par téléphone
        //     $clients = $clientRepository->findBy(['telephone' => $formSearch->get('telephone')->getData()]);
        //     $maxPage = 1;
        // }else{
        //     $clients = $clientRepository->paginateClients($page,$limit);
        //     $count = $clients->count();
        //     $maxPage = ceil($count/$limit);
        // }

        
    //     return $this->render('client/index.html.twig', [
    //         'clients' => $clients,
    //         'formSearch' => $formSearch->createView(),
    //         'page' => $page,//page actuelle
    //         'maxPage' => $maxPage,
    //     ]);
}
