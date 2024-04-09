<?php

namespace Haaruuyaa\BcaSnap\Controllers;

use Haaruuyaa\BcaSnap\Services\BcaSnapServices;

class BcaSnapController
{
    public function __construct(private readonly BcaSnapServices $services) {}
    public function getBalance(): string
    {
        return $this->services->balance();
    }
}
