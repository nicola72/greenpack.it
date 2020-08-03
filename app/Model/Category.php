<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Category extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'id',
        'macrocategory_id',
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
        return $website_config['protocol']."://".$_SERVER['HTTP_HOST']."/".$locale."/category/".$this->id;

    }

    public function macrocategory()
    {
        return $this->belongsTo('App\Model\Macrocategory');
    }

    public function product()
    {
        return $this->hasOne('App\Model\Product');
    }

    public function products()
    {
        return $this->hasMany('App\Model\Product')
            ->addSelect(\DB::raw('*'))
            ->addSelect(\DB::raw('IF(prezzo_scontato = \'0.00\', prezzo, prezzo_scontato) as minimal'));
    }

    public function pairing()
    {
        return $this->hasOne('App\Model\Pairing');
    }

    public function pairings()
    {
        return $this->hasMany('App\Model\Pairing');
    }

    public function pairings_for_list($tipo_filtro = false,$parametro_filtro = false)
    {
        $pairings = $this->hasMany('App\Model\Pairing')->where('visibile',1)->get();

        if($pairings)
        {
            foreach ($pairings as $key => &$pairing)
            {
                $product1 = Product::find($pairing->product1_id);
                $product2 = Product::find($pairing->product2_id);

                //se devo filtrarli per materiali della categoria
                if($tipo_filtro)
                {
                    if($tipo_filtro == 'material_chess')
                    {
                        $materials = $product1->category->materials;
                        $material_ids = [];
                        foreach ($materials as $material)
                        {
                            $material_ids[] = $material->id;
                        }
                        if(!in_array($parametro_filtro,$material_ids))
                        {
                            unset($pairings[$key]);
                            continue;
                        }
                    }
                    elseif($tipo_filtro == 'material_board')
                    {
                        $materials = $product2->category->materials;
                        $material_ids = [];
                        foreach ($materials as $material)
                        {
                            $material_ids[] = $material->id;
                        }
                        if(!in_array($parametro_filtro,$material_ids))
                        {
                            unset($pairings[$key]);
                            continue;
                        }
                    }
                }

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

    public function materials()
    {
        return $this->belongsToMany('App\Model\Material', 'category_material');
    }

}
