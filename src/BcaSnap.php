<?php

namespace Haaruuyaa\BcaSnap;

use Haaruuyaa\BcaSnap\Controllers\BcaSnapController;

class BcaSnap
{
    public function __construct(private readonly BcaSnapController $controller) {}
    public function balance()
    {
        return $this->controller->getBalance();
    }
}
