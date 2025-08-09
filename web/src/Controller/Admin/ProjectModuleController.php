<?php

namespace App\Controller\Admin;

use App\Entity\Admin\DateLog;
use App\Entity\Admin\Entity;
use App\Entity\Admin\ProjectModule;
use App\Form\Admin\ProjectModuleType;
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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/project/module')]
class ProjectModuleController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
        
    }

    #[Route('/', name: 'app_admin_project_module_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.id', [], 'project'), 'UTF-8'),
                'className' => 'text-center',
            ])
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'project'), 'UTF-8'),
            ])
            ->add('publication', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.publication'), 'UTF-8'),
                'className' => 'text-center',
                'render' => function ($value, $context) {
                    $publication = $context->getPublication();

                    if ('' === $publication) {
                        return mb_strtolower($this->translator->trans('publication.select'), 'UTF-8');
                    } elseif (0 == $publication) {
                        return '<span class="badge bg-danger">'.mb_strtolower($this->translator->trans('publication.unpublish'), 'UTF-8').'</span>';
                    } elseif (1 == $publication) {
                        return '<span class="badge bg-success">'.mb_strtolower($this->translator->trans('publication.publish'), 'UTF-8').'</span>';
                    }

                    return '';
                },
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router) {
                    $showUrl = $router->generate('app_admin_project_module_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_module_edit', ['id' => $context->getId()]);

                    return sprintf('
                        <div class="text-center">
                            <a href="%s" title="%s"><i class="mdi mdi-eye"></i></a>
                            <a href="%s" title="%s"><i class="mdi mdi-pen"></i></a>
                        </div>',
                        $showUrl, mb_strtolower($this->translator->trans('action.show'), 'UTF-8'),
                        $editUrl, mb_strtolower($this->translator->trans('action.edit'), 'UTF-8')
                    );
                },
            ])
            ->handleRequest($request)
            ->createAdapter(ORMAdapter::class, [
                'entity' => ProjectModule::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('tb')
                        ->from(ProjectModule::class, 'tb');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project_module/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_modules', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.overview_of_project_modules', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_module_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
    ): Response {
        $projectModule = new ProjectModule();

        $form = $this->createForm(ProjectModuleType::class, $projectModule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($projectModule);
            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'project_module'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($projectModule->getId());
            $dateLog->setUser($this->getUser());
            $dateLog->setUpdatedAt(new \DateTimeImmutable('1000-01-01 00:00:00'));

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_module_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_module/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_modules', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.new_project_module', [], 'project'),
            'projectModule' => $projectModule,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_module_show', methods: ['GET'])]
    public function show(
        ProjectModule $projectModule,
        Request $request,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'project_module'], []);

        $repository = $this->entityManager->getRepository(DateLog::class);
        $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')], ['updatedAt' => 'ASC']);

        return $this->render('admin/project_module/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_modules', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.project_module_detail', [], 'project'),
            'projectModule' => $projectModule,
            'user' => $this->getUser(),
            'dateLogs' => $dateLogs,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_module_edit', methods: ['GET', 'POST'])]
    public function edit(
        ProjectModule $projectModule,
        Request $request,
    ): Response {
        $form = $this->createForm(ProjectModuleType::class, $projectModule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'project_module'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($request->attributes->get('id'));
            $dateLog->setUser($this->getUser());
            $dateLog->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_module_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_module/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_modules', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_project_module', [], 'project'),
            'projectModule' => $projectModule,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_module_delete', methods: ['POST'])]
    public function delete(
        ProjectModule $projectModule,
        Request $request,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$projectModule->getId(), $request->getPayload()->get('_token'))) {
            $this->entityManager->remove($projectModule);

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'project_module'], []);

            // delete date log
            $repository = $this->entityManager->getRepository(DateLog::class);
            $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')], ['updatedAt' => 'ASC']);

            if ($dateLogs) {
                foreach ($dateLogs as $dateLog) {
                    $this->entityManager->remove($dateLog);
                }
            }

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_project_module_index', [], Response::HTTP_SEE_OTHER);
    }
}
