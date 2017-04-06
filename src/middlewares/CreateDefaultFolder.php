<?php

namespace Jeylabs\Vaultbox\middlewares;

use Jeylabs\Vaultbox\traits\VaultboxHelpers;
use Closure;

class CreateDefaultFolder
{
    use VaultboxHelpers;

    public function handle($request, Closure $next)
    {
        $this->checkDefaultFolderExists('user');
        $this->checkDefaultFolderExists('share');

        return $next($request);
    }

    private function checkDefaultFolderExists($type = 'share')
    {
        if ($type === 'user' && !$this->allowMultiUser()) {
            return;
        }

        if ($type === 'share' && (!$this->enabledShareFolder()  || !$this->allowMultiUser())) {
            return;
        }

        $path = $this->getRootFolderPath($type);

        $this->createFolderByPath($path);
    }
}
