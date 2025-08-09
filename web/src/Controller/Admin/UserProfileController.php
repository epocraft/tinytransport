<?php

namespace App\Controller\Admin;

use App\Entity\Admin\DateLog;
use App\Entity\Admin\Entity;
use App\Entity\Admin\User;
use App\Form\Admin\UserProfileType;
use App\Service\Admin\LanguageService;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/user')]
class UserProfileController extends AbstractController
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

    #[Route('/profile', name: 'app_admin_user_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        User $user,
    ): Response {
        // get entity
        $repository = $this->entityManager->getRepository(Entity::class);
        $entity = $repository->findOneBy(['name' => 'user'], []);

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Hashování hesla, pokud bylo zadáno nové
            $newPassword = $form->get('password')->getData();
            if (!empty($newPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            // 2. Aktualizace uživatele v databázi
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // 3. Aktualizace tokenu pro aktuální uživatelské sezení
            $currentToken = $this->tokenStorage->getToken();
            if ($currentToken && $currentToken->getUser() === $user) {
                // Vytvoření nového tokenu s aktuálním uživatelem a aktualizovanými rolemi
                $newToken = new UsernamePasswordToken(
                    $user,
                    'main', // Firewall name (název hlavního firewallu v security.yaml)
                    $user->getRoles()
                );
                $this->tokenStorage->setToken($newToken);
            }

            // date log
            $dateLog = new DateLog();
            $dateLog->setEntity($entity);
            $dateLog->setRecordId($user->getId());
            $dateLog->setUser($request->attributes->get('user'));
            $dateLog->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($dateLog);

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_changed')
            );

            return $this->redirectToRoute('app_admin_user_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user_profile/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $this->translator->trans('breadcrumbs.users', [], 'user'),
            'pageTitle' => $this->translator->trans('breadcrumbs.edit_user', [], 'user'),
            'user' => $user,
            'form' => $form,
        ]);
    }
}
