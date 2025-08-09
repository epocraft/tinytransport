<?php

namespace App\Controller\Admin;

use App\Entity\Admin\DateLog;
use App\Entity\Admin\Entity;
use App\Entity\Admin\Image;
use App\Form\Admin\ImageType;
use App\Service\Admin\LanguageService;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/image')]
class ImageController extends AbstractController
{
    private $client;

    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        HttpClientInterface $client,
    ) {
        $this->client = $client;
    }

    #[Route('/', name: 'app_admin_image_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
        TranslatorInterface $translatorInterface,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.id', [], 'image'), 'UTF-8'),
                'className' => 'text-center',
            ])
            ->add('image', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.image', [], 'image'), 'UTF-8'),
                'className' => 'text-center',
                'render' => function ($value, $context) {
                    $image = $context->getFilePath() . $context->getFileName() . '.' . $context->getFileType();
                    if (file_exists($image)) {
                        return '<a href="' . $image . '" title="' . $context->getName() . '" data-lightbox="' . $context->getName() . '"><img src="' . $image . '" alt="' . $context->getName() . '" width="20"></a>';
                    } else {
                        return '-';
                    }
                    
                },
            ])
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.name', [], 'image'), 'UTF-8'),
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
                    $showUrl = $router->generate('app_admin_image_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_image_edit', ['id' => $context->getId()]);

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
                'entity' => Image::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('pr')
                        ->from(Image::class, 'pr');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/image/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.images', [], 'image'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_images', [], 'image'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_image_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        $image = new Image();

        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($image);
            $entityManager->flush();

            // get entity
            $repository = $entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'image'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($image->getId());
            $dateLog->setUser($this->getUser());
            $dateLog->setUpdatedAt(new \DateTimeImmutable('1000-01-01 00:00:00'));

            $entityManager->persist($dateLog);

            $entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/image/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.images', [], 'image'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_image', [], 'image'),
            'image' => $image,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_image_show', methods: ['GET'])]
    public function show(
        EntityManagerInterface $entityManager,
        Image $image,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        // get entity
        $repository = $entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'image'], []);

        $repository = $entityManager->getRepository(DateLog::class);
        $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')], ['updatedAt' => 'ASC']);

        return $this->render('admin/image/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.images', [], 'image'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.image_detail', [], 'image'),
            'image' => $image,
            'user' => $this->getUser(),
            'dateLogs' => $dateLogs,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_image_edit', methods: ['GET', 'POST'])]
    public function edit(
        EntityManagerInterface $entityManager,
        Image $image,
        ParameterBagInterface $parameterBag,
        SluggerInterface $slugger,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        // získáme původní hodnotu před uložením
        $originalFileName = $image->getFileName();

        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($image->getFileName() !== $originalFileName) {

                // Získání nového názvu souboru z formuláře
                $newFileName = mb_strtolower($slugger->slug($form->get('fileName')->getData()), 'UTF-8');
                
                // Získání cesty k původnímu souboru
                $originalFilePath = $parameterBag->get('project_directory') . $image->getFilePath() . '/' . $originalFileName . '.' . $image->getFileType();
                
                // Cesta k novému souboru
                $newFilePath = $parameterBag->get('project_directory') . $image->getFilePath() . '/' . $newFileName . '.' . $image->getFileType();

                if (file_exists($newFilePath)) {
                    $this->addFlash('error', 'Soubor s tímto názvem již existuje.');
                } else {
                    if (rename($originalFilePath, $newFilePath)) {
                        // Aktualizace entity
                        $image->setFileName($newFileName);
                        $this->addFlash('success', 'Soubor byl úspěšně přejmenován.');
                    } else {
                        $this->addFlash('error', 'Soubor nebyl přejmenován.');
                    }
                }
            }

            $entityManager->flush();

            // get entity
            $repository = $entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'image'], []);

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

            return $this->redirectToRoute('app_admin_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/image/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.images', [], 'image'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_image', [], 'image'),
            'image' => $image,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_image_delete', methods: ['POST'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Image $image,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->getPayload()->get('_token'))) {

            //$entityManager->remove($image);

            // get entity
            $repository = $entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'image'], []);

            // delete image
            $image = $entityManager->getRepository(Image::class)->findOneBy(['entity' => $entity, 'id' => $request->attributes->get('id')]);
            if ($image) {
                $filePath = $this->getParameter('images_absolute_directory') . '/' . $image->getFileName() . '.' . $image->getFileType();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $entityManager->remove($image);
            }

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

        return $this->redirectToRoute('app_admin_image_index', [], Response::HTTP_SEE_OTHER);
    }
}
