<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ProjectContact;
use App\Form\Admin\ProjectContactType;
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

#[Route('/admin/project/contact')]
class ProjectContactController extends AbstractController
{
    private $client;

    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    ) {
        
    }

    #[Route('/', name: 'app_admin_project_contact_index', methods: ['GET', 'POST'])]
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
            ->add('piName', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.pi_name', [], 'project'), 'UTF-8'),
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router) {
                    $showUrl = $router->generate('app_admin_project_contact_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_contact_edit', ['id' => $context->getId()]);
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
                'entity' => ProjectContact::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('prc')
                        ->from(ProjectContact::class, 'prc');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project_contact/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.overview_of_project_contacts', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_contact_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
    ): Response {
        $projectContact = new ProjectContact();

        $form = $this->createForm(ProjectContactType::class, $projectContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($projectContact);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_contact/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.new_project_contact', [], 'project'),
            'projectContact' => $projectContact,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_contact_show', methods: ['GET'])]
    public function show(
        ProjectContact $projectContact,
    ): Response {
        return $this->render('admin/project_contact/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.project_contact_detail', [], 'project'),
            'projectContact' => $projectContact,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_contact_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        ProjectContact $projectContact,
    ): Response {
        $form = $this->createForm(ProjectContactType::class, $projectContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project_contact/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.project_contacts', [], 'project'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_project_contact', [], 'project'),
            'projectContact' => $projectContact,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_contact_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ProjectContact $projectContact,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$projectContact->getId(), $request->request->get('_token'))) {

            $this->entityManager->remove($projectContact);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_deleted')
            );

        }

        return $this->redirectToRoute('app_admin_project_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
