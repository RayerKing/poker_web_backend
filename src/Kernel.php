<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        // Přesměruje cache do systémové /tmp složky v Linuxu a izoluje ji podle uživatele (root vs www-data)
        return sys_get_temp_dir() . '/poker_cache/' . get_current_user() . '/' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        // Jako pojistku přesměrujeme i logovací složku do /tmp, pokud by do ní chtěl zapisovat jiný bundle uvnitř Symfony
        return sys_get_temp_dir() . '/poker_logs/' . get_current_user() . '/' . $this->getEnvironment();
    }
}
