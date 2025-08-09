<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectText;
use App\Form\Admin\ProjectTextType;
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

#[Route('/admin/project/text')]
class ProjectTextController extends AbstractController
{
    private $client;

    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        HttpClientInterface $client,
    ) {
        $this->client = $client;
    }

    #[Route('/', name: 'app_admin_project_text_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.id', [], 'project'), 'UTF-8'),
                'className' => 'text-center'
            ])
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'project'), 'UTF-8'),
            ])
            ->add('description', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.description', [], 'project'), 'UTF-8'),
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router) {
                    $showUrl = $router->generate('app_admin_project_text_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_text_edit', ['id' => $context->getId()]);
                    return sprintf('
                        <div class="text-center">
                            <a href="%s" title="%s"><i class="mdi mdi-eye"></i></a>
                            <a href="%s" title="%s"><i class="mdi mdi-pen"></i></a>
                        </div>',
                        $showUrl, mb_strtolower($this->translator->trans('action.show'), 'UTF-8'),
                        $editUrl, mb_strtolower($this->translator->trans('action.edit'), 'UTF-8')
                    );
                }
            ])
            ->handleRequest($request)
            ->createAdapter(ORMAdapter::class, [
                'entity' => ProjectText::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('prt')
                        ->from(ProjectText::class, 'prt');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project_text/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.overview_of_project_texts', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_text_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
    ): Response {
        $projectText = new ProjectText();
        
        $form = $this->createForm(ProjectTextType::class, $projectText);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($projectText);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_text_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_text/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.new_project_text', [], 'project'),
            'projectText' => $projectText,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_text_show', methods: ['GET'])]
    public function show(
        ProjectText $projectText,
    ): Response {
        return $this->render('admin/project_text/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.project_text_detail', [], 'project'),
            'projectText' => $projectText,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_text_edit', methods: ['GET', 'POST'])]
    public function edit(
        ProjectText $projectText,
        Request $request,
    ): Response {
        $form = $this->createForm(ProjectTextType::class, $projectText);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_text_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_text/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_texts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_project_text', [], 'project'),
            'projectText' => $projectText,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_text_delete', methods: ['POST'])]
    public function delete(
        ProjectText $projectText,
        Request $request,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$projectText->getId(), $request->request->get('_token'))) {

            $this->entityManager->remove($projectText);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_deleted')
            );

        }

        return $this->redirectToRoute('app_admin_project_text_index', [], Response::HTTP_SEE_OTHER);
    }
}
