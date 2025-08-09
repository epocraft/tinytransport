<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Project;
use App\Kernel;
use App\Service\Admin\LanguageService;
use App\Service\Admin\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
        private TranslatorInterface $translator,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {}

    #[Route('/admin/dashboard', name: 'app_admin_dashboard', methods: ['GET', 'POST'])]
    public function index(
        DataTableFactory $dataTableFactory,
        Request $request,
        UrlGeneratorInterface $router,
    ): Response {

        // get session
        $session = $this->requestStack->getSession();

        //$session->remove('project');

        // Zpracování POST požadavku, uložení projektu
        if ($request->isMethod('POST')) {
            $projectId = $request->request->get('projectId');
            if ($projectId) {
                // Uložení projectId do session
                $session->set('project', $projectId);

                // Přesměrování zpět na tuto stránku nebo jinam
                return $this->redirectToRoute('app_admin_dashboard');
            }
        }

        if (!$session->has('project')) {

            // get all projects
            $repository = $this->entityManager->getRepository(Project::class);
            $projects = $repository->findBy(['publication' => 1]);

            return $this->render('admin/dashboard/select_project.html.twig', [
                'page' => $this->translator->trans('breadcrumbs.select_project', [], 'dashboard'),
                'pageTitle' => $this->translator->trans('breadcrumbs.select_project', [], 'dashboard'),
                'projects' => $projects,
            ]);

        } else {

            // Získání aktuálního uživatele
            $user = $request->attributes->get('user');

            // Kontrola rolí uživatele
            /*if (!$user || !$this->isGranted('ROLE_ADMIN')) {
                // Pokud uživatel neexistuje nebo nemá roli ROLE_ADMIN (ani zděděnou), přesměrujte ho na vlastní chybovou stránku
                $this->addFlash('error', 'Nemáte dostatečná oprávnění pro přístup do této sekce.');
                return $this->redirectToRoute('app_admin_access_denied');
            }*/

            $separatorOfThousands = $user?->getUserUserSettings()?->getSeparatorOfThousands() ?? ' ';
            $decimalPoint = $user?->getUserUserSettings()?->getDecimalPoint() ?? ',';



            $system = array();
            $system["https"] =  $request->isSecure();
            $system["phpVersion"] = phpversion();
            $system["memoryLimit"] = ini_get('memory_limit');
            $system["postMaxSize"] = ini_get('post_max_size');
            $system["uploadMaxFilesize"] = ini_get('upload_max_filesize');
            $system["symfonyVersion"] = Kernel::VERSION;
            $system["twigVersion"] = \Twig\Environment::VERSION;

            // Extrakce hlavní verze Symfony (např. 7.1 z 7.1.x)
            $symfonyVersionParts = explode('.', Kernel::VERSION);
            $symfonyMainVersion = $symfonyVersionParts[0] . '.' . $symfonyVersionParts[1];

            // Dynamická URL pro získání JSON dat o konkrétní verzi Symfony
            $jsonUrl = 'https://symfony.com/releases/' . $symfonyMainVersion . '.json';

            // Načtení JSON dat z externího API
            $httpClient = HttpClient::create();
            try {
                $response = $httpClient->request('GET', $jsonUrl);
                $symfonyData = $response->toArray();
            } catch (\Exception $e) {
                // Pokud selže načtení dat, lze přidat zpracování chyby
                $this->addFlash('error', 'Nelze načíst informace o verzi Symfony.');
                $symfonyData = null; // Můžeme nastavit na null, pokud chceme šablonu zobrazit i bez dat
            }



            return $this->render('admin/dashboard/index.html.twig', [
                'languageService' => $this->languageService->getLanguage(),
                'projectService' => $this->projectService->getProject(),
                'page' => $this->translator->trans('breadcrumbs.dashboard', [], 'dashboard'),
                'pageTitle' => $this->translator->trans('breadcrumbs.dashboard', [], 'dashboard'),
                'system' => $system,
                'symfonyData' => $symfonyData,
            ]);

        }
    }
}
