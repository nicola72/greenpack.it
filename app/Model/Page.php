<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Page extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];
    protected $fillable = [
        'nome',
        'label_it',
        'label_en',
        'label_de',
        'label_fr',
        'label_es',
        'label_ru'
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

    public function label()
    {
        $locale = \App::getLocale();
        return $this->{'label_'.$locale};
    }

    public function is_current()
    {
        $locale = \App::getLocale();
        $urls = $this->morphMany('App\Model\Url','urlable');
        $url = $urls->where('locale',$locale)->first();
        if($_SERVER['REQUEST_URI'] == $locale."/".$url->slug)
        {
            return true;
        }
        return false;
    }
}
