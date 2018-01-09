<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    /**
     * The table that the model is stored in
     * @var string
     */
    protected $connection = 'article_library';

    /**
     * The table that the model is stored in
     * @var string
     */
    protected $table = 'ledger';

    /**
     * The fields that cannot be mass assigned. An empty array is required to
     * allow the model to be mass assigned.
     * @var array
     */
    protected $guarded = [];
}
