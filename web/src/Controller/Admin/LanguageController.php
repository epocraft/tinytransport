<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Language;
use App\Form\Admin\LanguageType;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/language')]
#[IsGranted('ROLE_SUPERADMIN')]
class LanguageController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
    ) {
        
    }

    #[Route('/', name: 'app_admin_language_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
        TranslatorInterface $translatorInterface,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.name', [], 'language'), 'UTF-8'),
                'className' => 'w-100 text-start',
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
                    $showUrl = $router->generate('app_admin_language_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_language_edit', ['id' => $context->getId()]);

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
                'entity' => Language::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('pr')
                        ->from(Language::class, 'pr');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/language/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.languages', [], 'language'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_languages', [], 'language'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_language_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($language);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/language/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.languages', [], 'language'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_language', [], 'language'),
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_language_show', methods: ['GET'])]
    public function show(
        Language $language,
        TranslatorInterface $translatorInterface,
    ): Response {
        return $this->render('admin/language/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.languages', [], 'language'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.language_detail', [], 'language'),
            'language' => $language,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_language_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        Language $language,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/language/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.languages', [], 'language'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_language', [], 'language'),
            'language' => $language,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_language_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Language $language,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$language->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($language);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_language_index', [], Response::HTTP_SEE_OTHER);
    }
}
