<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Dette;
use App\Form\ApprovisionnementType;
use App\Entity\DetteArticle;
use App\Enum\StatutEnum;
use App\Entity\DemandeArticle;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemandeController extends AbstractController
{
    #[Route('/demandes', name: 'demande_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
{
    // Récupérer le statut sélectionné depuis la requête GET
    $statut = $request->query->get('statut', null);

    // Construire la requête avec filtre conditionnel
    $queryBuilder = $entityManager->getRepository(Demande::class)->createQueryBuilder('d');

    if ($statut) {
        $queryBuilder->andWhere('d.statut = :statut')
                     ->setParameter('statut', $statut);
    }

    $query = $queryBuilder->getQuery();

    // Récupérer la pagination des demandes
    $demandes = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1), // Page actuelle
        10 // Limite par page
    );

    // Récupérer la liste des articles
    $articles = $entityManager->getRepository(Article::class)->findAll();

    return $this->render('demande/index.html.twig', [
        'demandes' => $demandes,
        'articles' => $articles,
    ]);
}

    #[Route('/demandes/add-demande', name: 'create_demande', methods: ['POST'])]
    public function createDemande(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = $request->request->all();

        if (empty($data['client_id']) || empty($data['articles'])) {
            $this->addFlash('error', 'Veuillez sélectionner un client et des articles.');
            return $this->redirectToRoute('add_demande');
        }

        // Récupération du client
        $client = $entityManager->getRepository(Client::class)->find($data['client_id']);
        if (!$client) {
            $this->addFlash('error', 'Client introuvable.');
            return $this->redirectToRoute('add_demande');
        }

        // Création de la nouvelle demande
        $demande = new Demande();
        $demande->setClient($client);
        $demande->setDate(new \DateTime());
        $demande->setStatut(StatutEnum::EN_COURS);

        // Assigner les champs obligatoires
        $demande->setTel((int) $client->getTelephone());
        $demande->setNomComplet($client->getPrenom() . ' ' . $client->getNom());

        // Création des articles liés avec leurs quantités
        $montantTotal = 0;
        foreach ($data['articles'] as $articleId => $quantity) {
            if ($quantity > 0) {
                $article = $entityManager->getRepository(Article::class)->find($articleId);
                if ($article) {
                    $demandeArticle = new DemandeArticle();
                    $demandeArticle->setDemande($demande);
                    $demandeArticle->setArticle($article);
                    $demandeArticle->setQuantite((int)$quantity);

                    $entityManager->persist($demandeArticle);

                    // Calculer le montant total
                    $montantTotal += $article->getPrix() * $quantity;
                }
            }
        }
        $demande->setMontant($montantTotal);

        // Sauvegarde de la demande
        $entityManager->persist($demande);
        $entityManager->flush();

        $this->addFlash('success', 'Demande créée avec succès.');
        return $this->redirectToRoute('add_demande');
    }

    #[Route('/demandes/{id}', name: 'demande_details', requirements: ['id' => '\d+'])]
    public function details(int $id, EntityManagerInterface $entityManager): Response
    {
        $demande = $entityManager->getRepository(Demande::class)->find($id);

        if (!$demande) {
            throw $this->createNotFoundException('Demande introuvable.');
        }

        return $this->render('demande/details.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/clients/check', name: 'check_client', methods: ['POST'])]
    public function checkClient(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom']) || empty($data['prenom']) || empty($data['telephone'])) {
            return new JsonResponse(['success' => false, 'message' => 'Veuillez remplir tous les champs.'], 400);
        }

        // Vérification du client dans la base de données
        $client = $entityManager->getRepository(Client::class)->findOneBy([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
        ]);

        if ($client) {
            return new JsonResponse(['success' => true, 'message' => 'Client existant.', 'clientId' => $client->getId()]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Client introuvable.'], 404);
    }

    #[Route('/demandes/add', name: 'add_demande', methods: ['GET'])]
    public function showAddDemande(EntityManagerInterface $entityManager): Response
    {
        // Récupérer la liste des articles disponibles
        $articles = $entityManager->getRepository(Article::class)->findAll();
        // Récupérer tous les clients existants
        $clients = $entityManager->getRepository(Client::class)->findAll();

        return $this->render('demande/addDemande.html.twig', [
            'articles' => $articles,
            'clients' => $clients,
        ]);
    }
    #[Route('/dette/valider/{demandeId}', name: 'demande_valider')]
public function validerDemande(int $demandeId, EntityManagerInterface $entityManager): Response
{
    // Récupérer la demande
    $demande = $entityManager->getRepository(Demande::class)->find($demandeId);

    if (!$demande) {
        throw $this->createNotFoundException('Demande introuvable.');
    }

    // Créer une nouvelle dette à partir de la demande
    $dette = new Dette();
    $dette->setClient($demande->getClient());
    $dette->setMontant($demande->getMontant());
    $dette->setDate(new \DateTime());
    $dette->setStatut('non_solde');
    $dette->setMontantVerser(0);

    // Copier les articles de la demande vers la dette
    foreach ($demande->getDemandeArticles() as $demandeArticle) {
        $detteArticle = new DetteArticle();
        $detteArticle->setArticle($demandeArticle->getArticle());
        $detteArticle->setQuantite($demandeArticle->getQuantite());
        $dette->addDetteArticle($detteArticle);
    }

    $entityManager->persist($dette);
    $entityManager->flush();

    $this->addFlash('success', 'Demande validée et convertie en dette avec succès.');

    return $this->redirectToRoute('dette_details', ['id' => $dette->getId()]);
}

#[Route('/demandes/{id}/annuler', name: 'demande_annuler')]
public function annulerDemande(int $id, EntityManagerInterface $entityManager): Response
{
    $demande = $entityManager->getRepository(Demande::class)->find($id);

    if (!$demande) {
        throw $this->createNotFoundException('Demande introuvable.');
    }

    // Mettre à jour le statut de la demande
    $demande->setStatut(StatutEnum::ANNULE);

    $entityManager->persist($demande);
    $entityManager->flush();

    $this->addFlash('warning', 'Demande annulée.');
    return $this->redirectToRoute('demande_details', ['id' => $id]);
}
}
