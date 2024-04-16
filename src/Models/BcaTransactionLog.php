<?php

namespace Haaruuyaa\BcaSnap\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class BcaTransactionLog extends Model
{
    use HasFactory;

    protected $table = 'bca_transaction_log';

    protected $guarded = ['id'];
}
