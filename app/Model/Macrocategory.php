<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Macrocategory extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'nome_it',
        'nome_en',
        'nome_de',
        'nome_fr',
        'nome_es',
        'nome_ru',
        'desc_it',
        'desc_en',
        'desc_de',
        'desc_fr',
        'desc_es',
        'desc_ru',
        'stato'
    ];

    public function urls()
    {
        return $this->morphMany('App\Model\Url','urlable');
    }

    public function url()
    {
        $locale = \App::getLocale();
        $urls = $this->morphMany('App\Model\Url','urlable');
        $url = $urls->where('locale',$locale)->first();
        $website_config = \Config::get('website_config');
        return $website_config['protocol']."://www.".$url->domain->nome."/".$locale."/".$url->slug;
    }

    public function category()
    {
        return $this->hasOne('App\Model\Category');
    }

    public function categories()
    {
        return $this->hasMany('App\Model\Category');
    }

    public function products()
    {
        return $this->hasManyThrough('App\Model\Product','App\Model\Category')
            ->addSelect(\DB::raw('*'))
            ->addSelect(\DB::raw('products.id as id_product'))
            ->addSelect(\DB::raw('IF(prezzo_scontato = \'0.00\', prezzo, prezzo_scontato) as minimal'));
    }

    public function pairings()
    {
        return $this->hasManyThrough('App\Model\Pairing','App\Model\Category');
    }

    public function pairings_for_list()
    {
        $pairings = $this->hasManyThrough('App\Model\Pairing','App\Model\Category')->where('visibile',1)->get();

        if($pairings)
        {
            foreach ($pairings as &$pairing)
            {
                $product1 = Product::find($pairing->product1_id);
                $product2 = Product::find($pairing->product2_id);

                $is_scontato = false;
                if($product1->prezzo_scontato != '0.00' || $product2->prezzo_scontato)
                {
                    $is_scontato = true;
                }

                $prezzo_product1 = ($product1->prezzo_scontato != '0.00') ? $product1->prezzo_scontato : $product1->prezzo;
                $prezzo_product2 = ($product2->prezzo_scontato != '0.00') ? $product2->prezzo_scontato : $product2->prezzo;

                $pairing->prezzo = $prezzo_product1 + $prezzo_product2;
                $pairing->is_scontato = $is_scontato;
            }
        }
        return $pairings;
    }
}
