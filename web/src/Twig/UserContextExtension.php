<?php

namespace App\Twig;

use App\Entity\Web\Quote;
use App\Repository\Web\OfferRepository;
use App\Repository\Web\QuoteRepository;
use App\Repository\Web\ServiceProviderUserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserContextExtension extends AbstractExtension
{
    public function __construct(
        private Security $security,
        private QuoteRepository $quotes,
        private OfferRepository $offers,
        private ServiceProviderUserRepository $spUsers
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_context', [$this, 'getContext']),
        ];
    }

    public function getContext(): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            return [
                'isAuthenticated' => false,
                'isProvider' => false,
                'quotes' => ['total' => 0],
                'offers' => ['total' => 0],
            ];
        }

        // Quotes counts (owned by user)
        $qb = $this->quotes->createQueryBuilder('q')
            ->select('q.status AS s, COUNT(q.id) AS c')
            ->andWhere('q.user = :u')->setParameter('u', $user)
            ->groupBy('q.status');
        $qCountsRaw = $qb->getQuery()->getResult();
        $qCounts = ['open'=>0, 'selected'=>0, 'closed'=>0, 'cancelled'=>0];
        foreach ($qCountsRaw as $row) { $qCounts[$row['s']] = (int)$row['c']; }
        $qCounts['total'] = array_sum($qCounts);

        // Provider IDs managed by user
        $links = $this->spUsers->findProvidersByUser($user);
        $providerIds = array_map(fn($l) => $l->getServiceProvider()->getId(), $links);
        $isProvider = !empty($providerIds);

        // Offers counts (as provider)
        $oCounts = ['active'=>0, 'accepted'=>0, 'rejected'=>0, 'withdrawn'=>0, 'expired'=>0, 'total'=>0];
        if ($providerIds) {
            $ob = $this->offers->createQueryBuilder('o')
                ->select('o.status AS s, COUNT(o.id) AS c')
                ->andWhere('o.serviceProvider IN (:pids)')->setParameter('pids', $providerIds)
                ->groupBy('o.status');
            $oCountsRaw = $ob->getQuery()->getResult();
            foreach ($oCountsRaw as $row) { $oCounts[$row['s']] = (int)$row['c']; }
            $oCounts['total'] = array_sum($oCounts) - $oCounts['total']; // odvoď total z ostatních
        }

        return [
            'isAuthenticated' => true,
            'isProvider'      => $isProvider,
            'quotes'          => $qCounts,
            'offers'          => $oCounts,
        ];
    }
}
