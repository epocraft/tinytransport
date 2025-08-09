<?php

namespace App\Controller\Admin;

use App\Entity\Admin\DateLog;
use App\Entity\Admin\Entity;
use App\Entity\Admin\Image;
use App\Entity\Admin\Project;
use App\Form\Admin\ProjectType;
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/admin/project')]
#[IsGranted('ROLE_SUPERADMIN')]
class ProjectController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $client,
        private LoaderInterface $loaderInterface,
        private CacheManager $cacheManager,
        private FilterManager $filterManager,
    ) {
        
    }
    
    #[Route('/', name: 'app_admin_project_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        TranslatorInterface $translatorInterface,
        UrlGeneratorInterface $router,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.id', [], 'project'), 'UTF-8'),
                'className' => 'text-center'
            ])
            ->add('ciName', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.ci_name', [], 'project'), 'UTF-8'),
            ])
            ->add('publication', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.publication'), 'UTF-8'),
                'className' => 'text-center',
                'render' => function ($value, $context) use ($translatorInterface) {
                    $publication = $context->getPublication();
                    if ($publication === '') {
                        return mb_strtolower($translatorInterface->trans('publication.select'), 'UTF-8');
                    } elseif ($publication == 0) {
                        return '<span class="badge bg-danger">' . mb_strtolower($translatorInterface->trans('publication.unpublish'), 'UTF-8') . '</span>';
                    } elseif ($publication == 1) {
                        return '<span class="badge bg-success">' . mb_strtolower($translatorInterface->trans('publication.publish'), 'UTF-8') . '</span>';
                    }
                    return '';
                }
            ])
            ->add('actions', TextColumn::class, [
                'label' => mb_strtolower($translatorInterface->trans('label.actions'), 'UTF-8'),
                'className' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'render' => function ($value, $context) use ($router, $translatorInterface) {
                    $showUrl = $router->generate('app_admin_project_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_project_edit', ['id' => $context->getId()]);
                    return sprintf('
                        <div class="text-center">
                            <a href="%s" title="%s"><i class="mdi mdi-eye"></i></a>
                            <a href="%s" title="%s"><i class="mdi mdi-pen"></i></a>
                        </div>',
                        $showUrl, mb_strtolower($translatorInterface->trans('action.show'), 'UTF-8'),
                        $editUrl, mb_strtolower($translatorInterface->trans('action.edit'), 'UTF-8')
                    );
                }
            ])
            ->handleRequest($request)
            ->createAdapter(ORMAdapter::class, [
                'entity' => Project::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('pr')
                        ->from(Project::class, 'pr');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/project/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.overview_of_projects', [], 'project'),
            'datatable' => $table,
        ]);
    }

    #[Route('/new', name: 'app_admin_project_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SluggerInterface $slugger,
        TranslatorInterface $translatorInterface,
    ): Response {
        $project = new Project();

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($project);
            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'project'], []);

            /** @var UploadedFile $photo */
            $photo = $form['photo']->getData();
            if ($photo) {
                $this->processImage(
                    $photo,
                    $entity,
                    $project->getId(),
                    $project->getCiName(),
                    $slugger
                );
            }

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.new_project', [], 'project'),
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_show', methods: ['GET'])]
    public function show(
        Project $project,
        TranslatorInterface $translatorInterface,
    ): Response {
        return $this->render('admin/project/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.project_detail', [], 'project'),
            'project' => $project,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_project_edit', methods: ['GET', 'POST'])]
    public function edit(
        Project $project,
        Request $request,
        SluggerInterface $slugger,
        TranslatorInterface $translatorInterface,
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
		    $entity = $repository->findOneBy(['name' => 'project'], []);

            /** @var UploadedFile $photo */
            $photo = $form['photo']->getData();
            if ($photo) {
                $this->processImage(
                    $photo,
                    $entity,
                    $project->getId(),
                    $project->getCiName(),
                    $slugger
                );
            }

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/project/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.projects', [], 'project'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.edit_project', [], 'project'),
            'project' => $project,
            'form' => $form,
            'image' => $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $this->entityManager->getRepository(Entity::class)->findOneBy(['name' => 'project'], []), 'recordId' => $request->attributes->get('id')]),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_project_delete', methods: ['POST'])]
    public function delete(
        Project $project,
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            
            $this->entityManager->remove($project);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $translatorInterface->trans('message.data_has_been_successfully_deleted')
            );
        }

        return $this->redirectToRoute('app_admin_project_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new-ares', name: 'app_admin_project_new_ares', methods: ['GET'])]
    public function newAres(
        Request $request,
        TranslatorInterface $translatorInterface,
    ): Response {

        $ico = $request->query->get('ico');
        $validator = Validation::createValidator();
        $constraint = new Assert\Length(['min' => 8, 'max' => 8]);
        $constraint = new Assert\Regex(['pattern' => '/^\d{8}$/']);

        $violations = $validator->validate($ico, [
            $constraint,
        ]);

        if (0 !== count($violations)) {

            // V případě chyby validace vrátit chybovou odpověď
            return $this->json(['error' => $translatorInterface->trans('error.in')], Response::HTTP_BAD_REQUEST);

        } else {

            $response = $this->client->request(
                'GET',
                'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/' . $request->query->get('ico'),
                [
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                ]
            );

        }

        if ($response->getStatusCode() === 200) {

            $content = $response->getContent();
            $data = json_decode($content, true);
            
            if ($data) {
                return $this->json($data);
            }

        }

        // V případě chyby nebo neexistujících dat vrátit chybovou odpověď
        return $this->json(['error' => $translatorInterface->trans('error.ares_in_required')], Response::HTTP_NOT_FOUND);
    }

    #[Route('/image/{recordId<\d+>}/delete', name: 'app_admin_project_image_delete', methods: ['POST'])]
    public function imageDelete(
        Request $request,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'project'], []);

        // delete image
        $image = $this->entityManager->getRepository(Image::class)->findOneBy(['entity' => $entity, 'recordId' => $request->attributes->get('recordId')]);
        if ($image) {
            $fileDir = $this->getParameter('projects_absolute_directory') . $request->attributes->get('recordId');
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
        $relativePath = $this->getParameter('projects_relative_directory') . $id . '/';
        $absolutePath = $this->getParameter('projects_absolute_directory') . $id . '/';
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
