<?php

namespace App\Service\Shared;

use App\Repository\Web\ServiceProviderRepository;

class ProviderAliasGenerator
{
    public function __construct(private ServiceProviderRepository $repo) {}

    public function generate(): string
    {
        // jednoduchý slovník – můžeš rozšířit
        $adj = ['rychly','tichy','bystry','pevny','sikovny','chytry','svizny','pohotovy','vesely','statecny'];
        $noun = ['jezev','vlk','rys','orel','zubr','los','bobr','netopyr','krecek','sokol'];

        for ($i=0; $i<50; $i++) {
            $alias = sprintf('%s-%s-%03d',
                $adj[array_rand($adj)],
                $noun[array_rand($noun)],
                random_int(0, 999)
            );
            if (!$this->repo->findOneBy(['alias' => $alias])) {
                return $alias;
            }
        }
        // fallback
        return 'poskytovatel-' . time() . '-' . random_int(100,999);
    }
}
