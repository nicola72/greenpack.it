<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;


class Catalog extends Model implements Sortable
{

    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'id',
        'nome_it',
        'nome_en',
        'nome_de',
        'nome_fr',
        'nome_es',
        'nome_ru',
        'visibile',
        'order',
    ];
}