<?php

namespace Haaruuyaa\BcaSnap\Controllers;

use Haaruuyaa\BcaSnap\BcaSnap;

class BcaSnapController
{
    public function getBalance(BcaSnap $bcaSnap): string
    {
        return $bcaSnap->balance();
    }
}
