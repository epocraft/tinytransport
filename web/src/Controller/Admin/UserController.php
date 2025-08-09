<?php

namespace App\Controller\Admin;

use App\Entity\Admin\DateLog;
use App\Entity\Admin\Entity;
use App\Entity\Admin\User;
use App\Form\Admin\UserType;
use App\Repository\Admin\UserRepository;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/user')]
#[IsGranted('ROLE_SUPERADMIN')]
final class UserController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private UserPasswordHasherInterface $passwordHasher,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage,
        private TranslatorInterface $translator,
    ) {
    }

    #[Route(name: 'app_admin_user_index', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        UserRepository $userRepository,
        Request $request,
        UrlGeneratorInterface $router,
    ): Response {
        $table = $dataTableFactory->create()
            ->add('email', TextColumn::class, [
                'label' => mb_strtolower($this->translator->trans('label.email', [], 'user'), 'UTF-8'),
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
                    $showUrl = $router->generate('app_admin_user_show', ['id' => $context->getId()]);
                    $editUrl = $router->generate('app_admin_user_edit', ['id' => $context->getId()]);

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
                'entity' => User::class,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('tb')
                        ->from(User::class, 'tb');
                },
            ]);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('admin/user/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.users', [], 'user'),
            'pageTitle' => $this->translator->trans('breadcrumbs.overview_of_users', [], 'user'),
            'datatable' => $table,
        ]);

        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
    ): Response {
        $userAccount = new User();

        $form = $this->createForm(UserType::class, $userAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userAccount->setLastLogin(new \DateTimeImmutable());

            // 1. Získání hesla z formuláře
            $plainPassword = $form->get('password')->getData();

            // 2. Hashování hesla
            if (!empty($plainPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($userAccount, $plainPassword);
                $userAccount->setPassword($hashedPassword);
            }

            $this->entityManager->persist($userAccount);
            $this->entityManager->flush();

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'user'], []);

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($userAccount->getId());
            $dateLog->setUser($request->attributes->get('user'));
            $dateLog->setUpdatedAt(new \DateTimeImmutable('1000-01-01 00:00:00'));

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.users', [], 'user'),
            'pageTitle' => $this->translator->trans('breadcrumbs.new_user', [], 'user'),
            'user' => $userAccount,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(
        User $userAccount,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'user'], []);

        $repository = $this->entityManager->getRepository(DateLog::class);
        $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $userAccount->getId()], ['updatedAt' => 'ASC']);

        return $this->render('admin/user/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.users', [], 'user'),
            'pageTitle' => $this->translator->trans('breadcrumbs.user_detail', [], 'user'),
            'user' => $userAccount,
            'dateLogs' => $dateLogs,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $userAccount,
        Request $request,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'user'], []);

        $form = $this->createForm(UserType::class, $userAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Hashování hesla, pokud bylo zadáno nové
            $newPassword = $form->get('password')->getData();
            if (!empty($newPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($userAccount, $newPassword);
                $userAccount->setPassword($hashedPassword);
            }

            // 2. Aktualizace uživatele v databázi
            $this->entityManager->persist($userAccount);
            $this->entityManager->flush();

            // 3. Aktualizace tokenu pro aktuální uživatelské sezení
            $currentToken = $this->tokenStorage->getToken();
            if ($currentToken && $currentToken->getUser() === $userAccount) {
                // Vytvoření nového tokenu s aktuálním uživatelem a aktualizovanými rolemi
                $newToken = new UsernamePasswordToken(
                    $userAccount,
                    'main', // Firewall name (název hlavního firewallu v security.yaml)
                    $userAccount->getRoles()
                );
                $this->tokenStorage->setToken($newToken);
            }

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($userAccount->getId());
            $dateLog->setUser($request->attributes->get('user'));
            $dateLog->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            if ($request->request->has('submit_update_and_back')) {
                return $this->redirectToRoute('app_admin_user_edit', ['id' => $userAccount->getId()], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('admin/user/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.users', [], 'user'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_user', [], 'user'),
            'user' => $userAccount,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $userAccount,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$userAccount->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($userAccount);

            // get entity
            $repository = $this->entityManager->getRepository(Entity::class);
            $entity = $repository->findOneBy(['name' => 'user'], []);

            // delete date log
            $repository = $this->entityManager->getRepository(DateLog::class);
            $dateLogs = $repository->findBy(['entity' => $entity, 'recordId' => $userAccount->getId()], ['updatedAt' => 'ASC']);

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

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
