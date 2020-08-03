<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Product extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'id',
        'category_id',
        'codice',
        'prezzo',
        'prezzo_scontato',
        'prezzo_fabbrica',
        'acquistabile',
        'acquistabile_italfama',
        'stock',
        'availability_id',
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
        'desc_breve_it',
        'desc_breve_en',
        'desc_breve_de',
        'desc_breve_fr',
        'desc_breve_es',
        'desc_breve_ru',
        'misure_it',
        'misure_en',
        'misure_de',
        'misure_fr',
        'misure_es',
        'misure_ru',
        'peso',
        'visibile',
        'italfama',
        'offerta',
        'novita',
        'order',
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

        if($url)
        {
            //url generata in automatico dal pannello presa dalla tabella urls
            return $website_config['protocol']."://www.".$url->domain->nome."/".$locale."/".$url->slug;
        }
        //nel caso non trovi l'url generata dal sistema nel db prendiamo l'url di salvataggio
        return $website_config['protocol']."://".$_SERVER['HTTP_HOST']."/".$locale."/details/".$this->id;

    }

    public function category()
    {
        return $this->belongsTo('App\Model\Category');
    }

    public function availability()
    {
        return $this->belongsTo('App\Model\Availability');
    }

    public function images()
    {
        return $this->morphMany('App\Model\File','fileable');
    }

    public function is_scontato()
    {
        if($this->prezzo_scontato != '0.00' && $this->prezzo_scontato != '')
        {
            return true;
        }
        return false;
    }

    public function cover()
    {
        $images = $this->morphMany('App\Model\File','fileable');
        if($images)
        {
            $images = $images->orderBy('order');
            $prima_img = $images->first();
            if(is_object($prima_img))
            {
                return $images->first()->path;
            }
        }
        return 'default.jpg';
    }

    public function prezzo_vendita()
    {
        if($this->prezzo_scontato != '0.00')
        {
            return $this->prezzo_scontato;
        }
        return $this->prezzo;
    }

}
