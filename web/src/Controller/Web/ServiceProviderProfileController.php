<?php

namespace App\Controller\Web;

use App\Entity\Web\Document;
use App\Entity\Web\Entity;
use App\Entity\Web\User;
use App\Entity\Web\UserContact;
use App\Entity\Web\UserDocument;
use App\Entity\Web\UserVehiclePark;
use App\Form\Web\UserContactType;
use App\Form\Web\UserDocumentType;
use App\Form\Web\UserProfileType;
use App\Form\Web\UserVehicleParkType;
use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use App\Service\Web\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ServiceProviderProfileController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private HttpClientInterface $client,
        private Filesystem $filesystem,
    ) {}

    #[Route('/{_locale<%app.supported_locales%>}/dopravce/profil', name: 'app_web_service_provider_profile')]
    public function index(Request $request): Response
    {
        /*$article = $this->entityManager->getRepository(Article::class)->findOneBy([
            'urlAlias' => 'dopravce-profil',
            'publication' => 1
        ]);*/

        /*if (!$article) {
            // Pokud článek s daným urlAliasem neexistuje, hodíme 404 chybu
            throw $this->createNotFoundException('Článek nebyl nalezen.');
        }*/

        return $this->render('web/service_provider/profile/' . $request->getLocale() . '/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            //'article' => $article,
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/dopravce/profil/zakladni-udaje', name: 'app_web_service_provider_profile_basic')]
    public function basic(Request $request): Response
    {
        $userContact = new UserContact();

        $existingContact = $this->entityManager->getRepository(UserContact::class)->findOneBy([
            'user' => $this->getUser(),
        ]);

        if ($existingContact) {
            $userContact = $existingContact;
        }

        $form = $this->createForm(UserContactType::class, $userContact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var \App\Entity\Web\User $user */
            $user = $this->getUser();

            $userContact->setUser($user);

            $this->entityManager->persist($userContact);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('message.data_has_been_successfully_inserted')
            );
            
            return $this->redirectToRoute('app_web_service_provider_profile_basic', [
                '_locale' => $request->getLocale(),
            ]);

        } else {

           

        }

        return $this->render('web/service_provider/profile/' . $request->getLocale() . '/contact.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'form' => $form,
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/dopravce/profil/prihlasovaci-udaje', name: 'app_web_service_provider_profile_login')]
    public function login(Request $request): Response
    {
        $quote = new User();

        /*$existingContact = $this->entityManager->getRepository(User::class)->findOneBy([
            'user' => $this->getUser(),
        ]);

        if ($existingContact) {
            $quote = $existingContact;
        }*/
        
        $form = $this->createForm(UserProfileType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($quote);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_web_service_provider_profile_login', [
                '_locale' => $request->getLocale(),
            ]);

        } else {

           

        }

        return $this->render('web/service_provider/profile/' . $request->getLocale() . '/login.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'form' => $form,
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/dopravce/profil/dokumenty', name: 'app_web_service_provider_profile_document')]
    public function document(
        Request $request,
        FileUploaderService $fileUploader,
    ): Response {
        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();

        // 1. Najdi existující dokumenty uživatele nebo vytvoř novou entitu
        $userDocument = $this->entityManager->getRepository(UserDocument::class)->findOneBy(['user' => $user]) ?? new UserDocument();
        
        // Pokud je to nová entita, přiřaď uživatele
        if (!$userDocument->getId()) {
            $userDocument->setUser($user);
        }

        // 2. Vytvoř formulář a předej mu entitu
        $form = $this->createForm(UserDocumentType::class, $userDocument);
        $form->handleRequest($request);
            
        // 3. ODSTRANILI JSME CELÝ BLOK PRO RUČNÍ ZPRACOVÁNÍ CHYB

        if ($form->isSubmitted() && $form->isValid()) {

            $relativePath = $this->getParameter('web')['documents_relative_directory'];
            $absolutePath = $this->getParameter('web')['documents_absolute_directory'];

            $fields = [
                'insuranceLiability',
                'insuranceTransport',
                'idCardFront',
                'idCardBack',
                'driverLicenseFront',
                'driverLicenseBack',
                'tradeLicense', // Přidáno pro kompletnost
            ];
            
            $wasFileUploaded = false; // Sledování, zda byl nahrán alespoň jeden soubor

            foreach ($fields as $fieldName) {
                /** @var UploadedFile|null $file */
                $file = $form->get($fieldName)->getData();

                if ($file instanceof UploadedFile) {

                    try {

                        $uploadResult = $fileUploader->upload($file, $absolutePath . $user->getId() . '/');
                        
                        // Dynamické volání setteru, např. setInsuranceLiability()
                        $setter = 'set' . ucfirst($fieldName);
                        if (method_exists($userDocument, $setter)) {
                            // Uložíme název souboru do hlavní entity UserDocument
                            $userDocument->{$setter}($uploadResult->newFilename);
                        }

                        // Vytvoření záznamu v obecné entitě Document
                        $document = new Document();
                        $document->setEntity($this->entityManager->getRepository(Entity::class)->findOneBy(['name' => 'user_document']));
                        $document->setRecordId($userDocument->getId());
                        $document->setName($uploadResult->originalName);
                        $document->setFileName($uploadResult->newFilename);
                        $document->setFileType($uploadResult->mimeType);
                        $document->setFilePath($relativePath . '/' . $user->getId() . '/' . $uploadResult->newFilename); // Upravená cesta
                        $document->setFileSize($uploadResult->size);
                        $document->setCreatedAt(new \DateTimeImmutable());
                        $document->setPublication(1);
                        $this->entityManager->persist($document);

                        $wasFileUploaded = true;

                    } catch (\Exception $e) {

                         $form->get($fieldName)->addError(new FormError('Chyba při nahrávání souboru.'));
                         // Zde už není potřeba nic víc, chyba se naváže na správné pole

                    }
                }
            }
            
            // Entitu UserDocument uložíme, jen pokud se nahrál nějaký soubor, nebo pokud už existovala
            if ($wasFileUploaded || $userDocument->getId()) {
                $this->entityManager->persist($userDocument);
            }
            
            $this->entityManager->flush();

            $this->addFlash('success', 'Dokumenty byly úspěšně uloženy.');

            return $this->redirectToRoute(
                'app_web_service_provider_profile_document',
                ['_locale' => $request->getLocale()]
            );
        }

        // Tento render obslouží jak první načtení stránky (GET), tak zobrazení po nevalidním odeslání (POST)
        return $this->render('web/service_provider/profile/' . $request->getLocale() . '/document.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'form' => $form->createView(),
            'userDocument' => $userDocument, // Předáme pro zobrazení již nahraných souborů
        ]);
    }

    #[Route(path: '/document/{id}/delete/{field}', name: 'app_web_service_provider_profile_document_delete', methods: ['POST'])]
    public function deleteField(
        Request      $request,
        int $id,
        string       $field
    ): Response {
        dd($id, $field);
        return new Response('ok', 200);
    }

    #[Route('/{_locale<%app.supported_locales%>}/dopravce/profil/vozovy-park', name: 'app_web_service_provider_profile_vehicle_park')]
    public function vehiclePark(Request $request): Response
    {
        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        // $userVehiclePark = $user->getVehicleParks(); // Příklad, jak získat kolekci vozidel z uživatele.
        // Není potřeba načítat entitu UserVehiclePark, protože formulář je vázán na User.

        // Pokud uživatel nemá žádné vozidlo, přidáme jedno prázdné
    /*if ($user->getUserUserVehicleParks()->isEmpty()) {
        $user->addUserUserVehiclePark(new UserVehiclePark());
    }*/
    
        $form = $this->createForm(UserVehicleParkType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) Projdu všechny vozidla a ty, která jsou nová (id === null), perzistuji ručně
            foreach ($user->getUserUserVehicleParks() as $vehiclePark) {
                if (null === $vehiclePark->getId()) {
                    // obousměrná vazba už je nastavená v addUserUserVehiclePark()
                    $this->entityManager->persist($vehiclePark);
                }
            }

            // 2) Pak už stačí flush – Doctrine vloží jak nová vozidla, tak případné smazané
            $this->entityManager->flush();

            $this->addFlash('success', 'Vozový park byl úspěšně aktualizován.');
            return $this->redirectToRoute('app_web_service_provider_profile_vehicle_park', [
                '_locale' => $request->getLocale(),
            ]);
        }

        // teprve při GETu, když je to poprvé, doplníme jeden prázdný
    if (!$request->isMethod('POST') && $user->getUserUserVehicleParks()->isEmpty()) {
        $user->addUserUserVehiclePark(new UserVehiclePark());
        // musíme „znovu“ vykreslit formulář se změnou
        $form = $this->createForm(UserVehicleParkType::class, $user);
    }

        return $this->render('web/service_provider/profile/' . $request->getLocale() . '/vehicle_park.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new-ares', name: 'app_web_service_provider_profile_contact_new_ares', methods: ['GET'])]
    public function newAres(
        Request $request,
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
            return $this->json(['error' => $this->translator->trans('error.in')], Response::HTTP_BAD_REQUEST);

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
        return $this->json(['error' => $this->translator->trans('error.ares_in_required')], Response::HTTP_NOT_FOUND);
    }
}
