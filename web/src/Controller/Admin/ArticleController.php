<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Article;
use App\Entity\Admin\DateLog;
use App\Entity\Admin\Entity;
use App\Entity\Admin\Image;
use App\Form\Admin\ArticleType;
use App\Service\Admin\LanguageService;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/article')]
class ArticleController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private LoaderInterface $loaderInterface,
        private CacheManager $cacheManager,
        private FilterManager $filterManager,
    ) {
        
    }

    #[Route('/', name: 'app_admin_article_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.id', [], 'article'), 'UTF-8'),
                'className' => 'text-center',
            ])
            ->add('name', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.name', [], 'article'), 'UTF-8'),
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
                    $showUrl = $router->generate('app_admin_article_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_article_edit', ['id' => $context->getId()]);

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
                'entity' => Article::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('tb')
                        ->from(Article::class, 'tb');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/article/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.articles', [], 'article'),
            'pageTitle' => $this->translator->trans('breadcrumbs.overview_of_articles', [], 'article'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_article_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SluggerInterface $slugger,
    ): Response {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setProject($this->projectService->getProject());
            $article->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'article'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($article->getId());
            $dateLog->setUser($request->attributes->get('user'));
            $dateLog->setUpdatedAt(new \DateTimeImmutable('1000-01-01 00:00:00'));

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();

            /** @var UploadedFile $photo */
            $photo = $form['photo']->getData();
            if ($photo) {
                $this->processImage(
                    $photo,
                    $entity,
                    $article->getId(),
                    $article->getName(),
                    $slugger
                );
            }

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/article/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.articles', [], 'article'),
            'pageTitle' => $this->translator->trans('breadcrumbs.new_article', [], 'article'),
            'article' => $article,
            'form' => $form,
            'image' => $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $this->entityManager->getRepository(Entity::class)->findOneBy(['name' => 'article'], []), 'recordId' => $request->attributes->get('id')]),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_article_show', methods: ['GET'])]
    public function show(
        Article $article,
        Request $request,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'article'], []);

        return $this->render('admin/article/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.articles', [], 'article'),
            'pageTitle' => $this->translator->trans('breadcrumbs.article_detail', [], 'article'),
            'article' => $article,
            'image' => $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $this->entityManager->getRepository(Entity::class)->findOneBy(['name' => 'price_list'], []), 'recordId' => $article->getId()]),
            'dateLogs' => $this->entityManager->getRepository(DateLog::class)->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')], ['updatedAt' => 'ASC']),
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_article_edit', methods: ['GET', 'POST'])]
    public function edit(
        Article $article,
        Request $request,
        SluggerInterface $slugger,
    ): Response {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'article'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($request->attributes->get('id'));
            $dateLog->setUser($request->attributes->get('user'));
            $dateLog->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();
            
            /** @var UploadedFile $photo */
            $photo = $form['photo']->getData();
            if ($photo) {
                $this->processImage(
                    $photo,
                    $entity,
                    $article->getId(),
                    $article->getName(),
                    $slugger
                );
            }
            
            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            if ($request->request->has('submit_update_and_back')) {
                return $this->redirectToRoute('app_admin_article_edit', ['id' => $article->getId()], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
            }

        }

        return $this->render('admin/article/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.articles', [], 'article'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_article', [], 'article'),
            'article' => $article,
            'form' => $form,
            'image' => $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $this->entityManager->getRepository(Entity::class)->findOneBy(['name' => 'article'], []), 'recordId' => $request->attributes->get('id')]),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_article_delete', methods: ['POST'])]
    public function delete(
        Article $article,
        Request $request,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->getPayload()->get('_token'))) {

            $this->entityManager->remove($article);

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'article'], []);

            // delete image
            $image = $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $entity, 'recordId' => $request->attributes->get('id')]);
            if ($image) {
                $filePath = $this->getParameter('images_absolute_directory') . '/' . $image->getFileName() . '.' . $image->getFileType();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $this->entityManager->remove($image);
            }

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

        return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/image/{recordId<\d+>}/delete', name: 'app_admin_article_image_delete', methods: ['POST'])]
    public function imageDelete(
        Request $request,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'article'], []);

        // delete image
        $image = $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $entity, 'recordId' => $request->attributes->get('recordId')]);
        if ($image) {
            $fileDir = $this->getParameter('articles_absolute_directory') . $request->attributes->get('recordId');
            $filePath = $fileDir . '/' . $image->getFileName() . '.' . $image->getFileType();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if (file_exists($fileDir)) {
                rmdir($fileDir);
            }
            $this->entityManager->remove($image);
        }

        // delete date log
        $repository = $this->entityManager->getRepository(DateLog::class);
        $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $request->attributes->get('recordId')], ['updatedAt' => 'ASC']);

        if ($dateLogs) {
            foreach ($dateLogs as $dateLog) {
                $this->entityManager->remove($dateLog);
            }
        }

        try {

            $this->entityManager->flush();

        } catch (\Exception $e) {

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to delete data from the database',
                'details' => $e->getMessage()
            ], 500);

        }
    
        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Image and related data deleted successfully'
        ]);
    }

    private function processImage(
        UploadedFile $photo,
        $entity,
        $id,
        $name,
        SluggerInterface $slugger
    ) {
        $relativePath = $this->getParameter('articles_relative_directory') . $id . '/';
        $absolutePath = $this->getParameter('articles_absolute_directory') . $id . '/';
        $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = mb_strtolower($slugger->slug($originalFilename), 'UTF-8') . '-' . uniqid();

        // Zkontrolujte, zda adresář existuje, a pokud ne, vytvořte ho
        if (!is_dir($absolutePath)) {
            mkdir($absolutePath, 0777, true); // 0777 jsou práva, 'true' zajistí rekurzivní vytvoření adresáře
        }

        // Kontrola a smazání existujícího obrázku
        $existingImage = $this->entityManager->getRepository(Image::class)->findOneBy([
            'entity' => $entity,
            'recordId' => $id
        ]);

        if ($existingImage) {
            // Smazání starého souboru z disku
            $oldFile = $absolutePath . $existingImage->getFileName() . '.' . $existingImage->getFileType();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            $image = $existingImage; // Použití existující entity pro aktualizaci
        } else {
            $image = new Image(); // Vytvoření nové entity, pokud neexistuje
        }

        try {

            // Uložení původního obrázku
            $uploadPath = $relativePath . $newFilename . '.' . $photo->guessExtension();
            $finalPath = $relativePath . $newFilename . '.webp';
            $photo->move($absolutePath, $newFilename . '.' . $photo->guessExtension());

            // Načtení a aplikace filtru pro konverzi na WebP
            $binary = $this->loaderInterface->find($uploadPath);  // Načtěte binární data obrázku
            $filteredBinary = $this->filterManager->applyFilter($binary, 'webp_conversion');
            $filteredFileContent = $filteredBinary->getContent();  // Získá obsah po filtraci

            // Uložení filtru do souboru
            file_put_contents($finalPath, $filteredFileContent);

            // Mazání dočasného původního souboru
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }

            $imageInfo = getimagesize($finalPath);

            $image->setEntity($entity);
            $image->setRecordId($id);
            $image->setName($name);
            $image->setDescription(null);
            $image->setVersion(null);
            $image->setFileName($newFilename);
            $image->setFilePath($relativePath);
            $image->setFileType('webp');
            $image->setFileSize(filesize($finalPath));
            $image->setWidth($imageInfo[0]);
            $image->setHeight($imageInfo[1]);
            $image->setCreatedAt(new \DateTimeImmutable());
            $image->setPublication(1);

            // Uložení změn
            $this->entityManager->persist($image);
            $this->entityManager->flush();

        } catch (FileException $e) {



        }

    }
}
