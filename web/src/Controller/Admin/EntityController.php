<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Entity;
use App\Form\Admin\EntityType;
use App\Service\Admin\LanguageService;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/entity')]
#[IsGranted('ROLE_SUPERADMIN')]
class EntityController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
        
    }

    /**
     * Displays a list of entities.
     *
     * @return Response The response object containing the rendered view.
     */
    #[Route('/', name: 'app_admin_entity_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
    ): Response {

        $table = $dataTableFactory->create()
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'entity'), 'UTF-8'),
                'className' => 'w-100 text-start',
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router) {
                    $showUrl = $router->generate('app_admin_entity_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_entity_edit', ['id' => $context->getId()]);

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
                'entity' => Entity::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('t_ent')
                        ->from(Entity::class, 't_ent');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/entity/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.entities', [], 'entity'),
            'pageTitle' => $this->translator->trans('breadcrumbs.overview_of_entities', [], 'entity'),
            'datatable' => $table,
        ]);

    }

    /**
     * Creates a new entity.
     *
     * @return Response The response object
     */
    #[Route('/new', name: 'app_admin_entity_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response {

        $entity = new Entity();

        $form = $this->createForm(EntityType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_entity_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('admin/entity/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.entities', [], 'entity'),
            'pageTitle' => $this->translator->trans('breadcrumbs.new_entity', [], 'entity'),
            'entity' => $entity,
            'form' => $form,
        ]);

    }

    /**
     * Displays the details of a specific entity.
     *
     * @param int $id The ID of the entity to display.
     * @return Response The response object containing the rendered view.
     */
    #[Route('/{id<\d+>}', name: 'app_admin_entity_show', methods: ['GET'])]
    public function show(Entity $entity): Response {

        return $this->render('admin/entity/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.entities', [], 'entity'),
            'pageTitle' => $this->translator->trans('breadcrumbs.entity_detail', [], 'entity'),
            'entity' => $entity,
        ]);

    }

    /**
     * Edit an entity.
     *
     * @param Request $request The HTTP request object
     * @param int $id The ID of the entity to edit
     * @return Response The HTTP response object
     */
    #[Route('/{id<\d+>}/edit', name: 'app_admin_entity_edit', methods: ['GET', 'POST'])]
    public function edit(
        Entity $entity,
        Request $request,
    ): Response {

        $form = $this->createForm(EntityType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_entity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/entity/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.entities', [], 'entity'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_entity', [], 'entity'),
            'entity' => $entity,
            'form' => $form,
        ]);

    }

    /**
     * Deletes an entity.
     *
     * @param Request $request The current request instance.
     * @param int $id The ID of the entity to delete.
     * @return Response The response after deletion.
     */
    #[Route('/{id<\d+>}', name: 'app_admin_entity_delete', methods: ['POST'])]
    public function delete(
        Entity $entity,
        Request $request,
    ): Response {

        if ($this->isCsrfTokenValid('delete'.$entity->getId(), $request->getPayload()->get('_token'))) {

            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_deleted')
            );

        }

        return $this->redirectToRoute('app_admin_entity_index', [], Response::HTTP_SEE_OTHER);

    }
}
