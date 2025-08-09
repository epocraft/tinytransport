<?php

namespace App\Controller\Admin;

use App\Entity\Admin\DateLog;
use App\Entity\Admin\Document;
use App\Entity\Admin\Entity;
use App\Form\Admin\DocumentType;
use App\Service\Admin\LanguageService;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/document')]
class DocumentController extends AbstractController
{
    private $client;

    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        HttpClientInterface $client,
    ) {
        $this->client = $client;
    }

    #[Route('/', name: 'app_admin_document_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
        TranslatorInterface $translatorInterface,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.id', [], 'document'), 'UTF-8'),
                'className' => 'text-center',
            ])
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.name', [], 'document'), 'UTF-8'),
            ])
            ->add('publication', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.publication'), 'UTF-8'),
                'className' => 'text-center',
                'render' => function ($value, $context) use ($translatorInterface) {
                    $publication = $context->getPublication();

                    if ('' === $publication) {
                        return mb_strtolower($translatorInterface->trans('publication.select'), 'UTF-8');
                    } elseif (0 == $publication) {
                        return '<span class="badge bg-danger">'.mb_strtolower($translatorInterface->trans('publication.unpublish'), 'UTF-8').'</span>';
                    } elseif (1 == $publication) {
                        return '<span class="badge bg-success">'.mb_strtolower($translatorInterface->trans('publication.publish'), 'UTF-8').'</span>';
                    }

                    return '';
                },
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router, $translatorInterface) {
                    $showUrl = $router->generate('app_admin_document_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_document_edit', ['id' => $context->getId()]);

                    return sprintf('
                        <div class="text-center">
                            <a href="%s" title="%s"><i class="mdi mdi-eye"></i></a>
                            <a href="%s" title="%s"><i class="mdi mdi-pen"></i></a>
                        </div>',
                        $showUrl, mb_strtolower($translatorInterface->trans('action.show'), 'UTF-8'),
                        $editUrl, mb_strtolower($translatorInterface->trans('action.edit'), 'UTF-8')
                    );
                },
            ])
            ->handleRequest($request)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Document::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('tb')
                        ->from(Document::class, 'tb');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/document/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.documents', [], 'document'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_documents', [], 'document'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_document_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        $document = new Document();

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($document);
            $entityManager->flush();

            // get entity
            $repository = $entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'document'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($document->getId());
            $dateLog->setUser($this->getUser());
            $dateLog->setUpdatedAt(new \DateTimeImmutable('1000-01-01 00:00:00'));

            $entityManager->persist($dateLog);

            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/document/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.documents', [], 'document'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_document', [], 'document'),
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_document_show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        Document $document,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        // get entity
        $repository = $entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'document'], []);

        $repository = $entityManager->getRepository(DateLog::class);
        $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')], ['updatedAt' => 'ASC']);

        return $this->render('admin/document/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.documents', [], 'document'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.document_detail', [], 'document'),
            'document' => $document,
            'user' => $this->getUser(),
            'dateLogs' => $dateLogs,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_document_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        Document $document,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // get entity
            $repository = $entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'document'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($request->attributes->get('id'));
            $dateLog->setUser($this->getUser());
            $dateLog->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($dateLog);

            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/document/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.documents', [], 'document'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_document', [], 'document'),
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_document_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Document $document,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($document);

            // get entity
            $repository = $entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'document'], []);

            // delete date log
            $repository = $entityManager->getRepository(DateLog::class);
            $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')], ['updatedAt' => 'ASC']);

            if ($dateLogs) {
                foreach ($dateLogs as $dateLog) {
                    $entityManager->remove($dateLog);
                }
            }

            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_document_index', [], Response::HTTP_SEE_OTHER);
    }
}
