<?php

namespace App\Http\Controllers\Website;

use App\Mail\Contact;
use App\Mail\RetriewPassword;
use App\Model\Cart;
use App\Model\Category;
use App\Model\Domain;
use App\Model\File;
use App\Model\Macrocategory;
use App\Model\Material;
use App\Model\Newsitem;
use App\Model\NewsletterSubscriber;
use App\Model\Order;
use App\Model\Page;
use App\Model\Pairing;
use App\Model\Product;
use App\Model\Review;
use App\Model\Seo;
use App\Model\Slider;
use App\Model\Style;
use App\Model\Website\UserDetail;
use App\Model\Wish;
use Illuminate\Http\Request;
use App\Model\Url;
use App\Http\Controllers\Controller;
use App\Service\GoogleRecaptcha;
use Illuminate\Pagination\Paginator;

class PageController extends Controller
{

    public function __construct()
    {

    }

    public function index(Request $request)
    {
        //per stabilire la lingua dobbiamo basarci sul dominio/alias
        $domain = $request->getHttpHost();
        $domain = str_replace("www.","",$domain);
        $domain = Domain::where('nome',$domain)->first();
        $locale = $domain->locale;
        \App::setLocale($locale);

        //prendo il seo quello flaggato come homepage e con la lingua stabilita sopra
        $seo = Seo::where('locale',$locale)->where('homepage',1)->first();

        //le slider che vengono da pannello
        $slider = Slider::where('visibile',1)->first();


        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();
        $prodotti_novita = Product::where('visibile',1)->where('availability_id','!=',2)->where('novita',1)->get();
        $abbinamenti_novita = Pairing::where('visibile',1)->where('novita',1)->get();
        $prodotti_offerta = Product::where('visibile',1)->where('availability_id','!=',2)->where('offerta',1)->get();
        $abbinamenti_offerta = Pairing::where('visibile',1)->where('offerta',1)->get();

        //la news che compare nella popup, nel pannello è quella flaggata come popup
        $popup = Newsitem::where('visibile',1)->where('popup',1)->first();


        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'slider' => $slider,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'prodotti_novita' => $prodotti_novita,
            'abbinamenti_novita' => $abbinamenti_novita,
            'prodotti_offerta' => $prodotti_offerta,
            'abbinamenti_offerta' => $abbinamenti_offerta,
            'popup'=> $popup
        ];
        return view('website.page.index',$params);
    }


    public function page(Request $request)
    {
        $slug = $request->segment(2);
        $lang = $request->segment(1);

        //prendo la url con quello slug e con la lingua
        $url = Url::where('slug',$slug)->where('locale',$lang)->first();

        if($url)
        {
            //se non siamo sul dominio giusto faccio il redirect
            if($url->domain->nome != $_SERVER['HTTP_HOST'] && "www.".$url->domain->nome != $_SERVER['HTTP_HOST'])
            {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: https://www.".$url->domain->nome."/".$url->locale."/".$url->slug);
                exit();
            }

            //stabilisco di che tipo è la url se per una pagina statica o per una categoria prodotto o ecc...
            switch ($url->urlable_type) {
                case 'App\Model\Page':
                    return $this->simplePage($request,$url);
                    break;
                case 'App\Model\Macrocategory':
                    return $this->macrocategoryPage($request,$url);
                    break;
                case 'App\Model\Category':
                    return $this->categoryPage($request,$url);
                    break;
                case 'App\Model\Product':
                    return $this->productPage($request,$url);
                    break;
                case 'App\Model\Pairing':
                    return $this->pairingPage($request,$url);
                    break;
                default:
                    return $this->page_404();
            }
        }
        else
        {
            return $this->page_404();
        }
    }

    /**
     * @param Request $request
     * @param $url
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * per le pagine statiche stabilite nel pannello nella sezione Page
     */
    protected function simplePage(Request $request,$url)
    {
        //cerca l'oggetto Page con l'id della url
        $page = Page::find($url->urlable_id);

        //poi chiama la funzione col nome della pagina
        if(method_exists($this,$page->nome))
        {
            return $this->{$page->nome}($request,$url);
        }
        else
        {
            return $this->page_404();
        }
    }

    protected function page_404()
    {
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();
        $params = [
            'carts' => $this->getCarts(),
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.errors.404',$params);
    }

    protected function macrocategoryPage(Request $request,$url)
    {
        $macrocategory = Macrocategory::find($url->urlable_id);

        $seo = $url->seo; //se ha un seo specifico
        //altrimenti cerco il seo generico per le categorie
        if(!$seo)
        {
            if($url->urlable_id == 23 || $url->urlable_id == 24 )
            {
                $segnaposto = trans('msg.scacchi').' '.$macrocategory->{'nome_'.\App::getLocale()};
            }
            elseif($url->urlable_id == 26)
            {
                $segnaposto = trans('msg.soldatini').' '.$macrocategory->{'nome_'.\App::getLocale()};
            }
            elseif($url->urlable_id == 25)
            {
                $segnaposto = trans('msg.statue').' '.$macrocategory->{'nome_'.\App::getLocale()};
            }
            else
            {
                $segnaposto = $macrocategory->{'nome_'.\App::getLocale()};
            }
            $seo = $this->getSeoGenerico('App\Model\Macrocategory', $segnaposto);
        }

        return $this->catalogo($request,$macrocategory,$macrocategory,$seo);
    }


    protected function categoryPage(Request $request,$url)
    {
        $category = Category::find($url->urlable_id);
        if(!$category)
        {
            return $this->page_404();
        }
        $macrocategory = Macrocategory::find($category->macrocategory_id);

        $seo = $url->seo; //se ha un seo specifico
        //altrimenti cerco il seo generico per le categorie
        if(!$seo)
        {
            $seo = $this->getSeoGenerico('App\Model\Category', $category->{'nome_'.\App::getLocale()});
        }

        return $this->catalogo($request,$macrocategory,$category,$seo);
    }

    protected function productPage(Request $request,$url)
    {
        $product = Product::find($url->urlable_id);
        if(!$product)
        {
            return $this->page_404();
        }

        $seo = $url->seo; //se ha un seo specifico
        //altrimenti cerco il seo generico per i prodotti
        if(!$seo)
        {
            $seo = $this->getSeoGenerico('App\Model\Product',$product->{'nome_'.\App::getLocale()});
        }

        //le macrocategorie per il menu nella colonna a sinistra
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null,
            'product' => $product,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view('website.page.product',$params);
    }

    protected function pairingPage(Request $request,$url)
    {
        $pairing = Pairing::find($url->urlable_id);
        if(!$pairing)
        {
            return $this->page_404();
        }

        $seo = $url->seo; //se ha un seo specifico
        //altrimenti cerco il seo generico per gli abbinamenti
        if(!$seo)
        {
            $seo = $this->getSeoGenerico('App\Model\Pairing', $pairing->{'nome_'.\App::getLocale()});
        }

        //le macrocategorie per il menu nella colonna a sinistra
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $pairing_correlati = Pairing::where('product1_id',$pairing->product1_id)->orWhere('product2_id',$pairing->product2_id)->get();
        $pairing_correlati = $pairing_correlati->where('id','!=',$pairing->id);

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null,
            'pairing' => $pairing,
            'pairing_correlati' => $pairing_correlati,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view('website.page.pairing',$params);
    }

    /**
     * @param Request $request
     * @param $macrocategory
     * @param $model
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * Funzione per visualizzare le pagine Categorie e Macrocategorie
     * Può essere una lista di Prodotti Singoli o Abbinamenti
     * Può essere chiamata normalmente oppure tramite ajax cliccando sul paginatore
     */
    protected function catalogo(Request $request,$macrocategory,$model,$seo = null)
    {
        //il parametro $model può essere o un App\Model|Macrocategory o un App\Model\Category
        //se il model è di tipo App\Model\Category allora vuol dire che siamo in una pagina categoria
        $category = ($model instanceof Category) ? $model : false;

        //le configurazioni varie del sito web
        $website_config = \Config::get('website_config');

        //quanti prodotti per pagina
        $per_page = $website_config['num_prod_per_page'];

        //le macrocategorie per il menu nella colonna a sinistra
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        //il template della vista in base a se è Ajax o no
        //NO SCROLL
        //$view = ($request->ajax()) ? 'website.page.partials.other_products' : 'website.page.product_list';
        //PER SCROLL
        $view = ($request->ajax()) ? 'website.page.partials.other_products_on_scroll' : 'website.page.product_list';

        //parametro per ORDINAMENTO prodotti  (dalla url o dalla sessione se ajax)
        //false se non settato
        $ordinamento = $this->getOrdinamentoParam($request);

        //parametro FILTRO prodotti (solo per gli abbinamenti sono: x Stile, x Materiale Scacchi, x Materiale Scacchiera)
        //false se non settato
        $filtro = $this->getFiltroParam($request);

        //parametro PAGE del paginatore dalla url se AJAX altrimenti dalla sessione
        //false se non settato
        $page = $this->getPageParam($request, $model);

        //inserisco i vari parametri nella sessione: Page se Ajax, Ordinamento e Filtro se chiamata normale
        $this->setParamsInSession($request, $ordinamento, $filtro, $page, $model);

        //se settata nella sessione la page la inseriamo nel paginatore (SERVE per il torna indietro del browser)
        //nel caso del caricamento on scroll non la setto
        //$this->updatePaginator($request,$page);

        $styles = Style::all();
        $chess_materials = Material::where('per','scacchi')->get();
        $board_materials = Material::where('per','scacchiera')->get();

        //se la macro id è 22 allora siamo su una categoria ABBINAMENTI
        if($macrocategory->id == 22)
        {
            //nessun prodotto singolo
            $products = false;
            //il totale degli abbinamenti presenti in questa categoria o macrocategoria
            $totali = $this->getTotalPairings($model,$filtro);
            //gli abbinamenti in base alla pagina ai filtri e all'ordinamento
            $pairings = $this->getPairings($model,$filtro,$ordinamento,$per_page);
        }
        //PRODOTTI SINGOLI
        else
        {
            //nessun abbinamento
            $pairings = false;
            //il totale prodotti presenti in questa categoria o macrocategoria
            $totali = $this->getTotalProducts($model);
            //i prodotti in base alla pagina e all'ordinamento
            $products = $this->getProducts($model,$ordinamento,$per_page);
        }

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => $macrocategory,
            'macrocategorie' => $macrocategorie,
            'macro_request' => $macrocategory->id,
            'category' => $category,
            'totali' => $totali,
            'titolo' => $model->{'nome_'.\App::getLocale()},
            'products' => $products,
            'pairings' => $pairings,
            'ordinamento' => $ordinamento,
            'descrizione_categoria' => $model->{'desc_'.\App::getLocale()},
            'styles' => $styles,
            'chess_materials' => $chess_materials,
            'board_materials' => $board_materials,
            'filtro' => $filtro,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view($view,$params);
    }

    //ATTENZIONE questa è l'url EMERGENZA categoria viene chiamata solo nel caso la categoria non abbia la url impostata nel db
    protected function category(Request $request)
    {
        $category = Category::find($request->id);
        if(!$category)
        {
            $this->page_404();
        }

        $macrocategory = Macrocategory::find($category->macrocategory_id);

        $seo = $this->getSeoGenerico('App\Model\Category', $category->{'nome_'.\App::getLocale()});

        return $this->catalogo($request,$macrocategory,$category,$seo);
    }

    //ATTENZIONE questa è l'url EMERGENZA scheda prodotto viene chiamata solo nel caso la categoria non abbia la url impostata nel db
    protected function details(Request $request)
    {
        $product = Product::find($request->id);
        if(!$product)
        {
            $this->page_404();
        }
        $seo = $this->getSeoGenerico('App\Model\Product',$product->{'nome_'.\App::getLocale()});

        //le macrocategorie per il menu nella colonna a sinistra
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null,
            'product' => $product,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view('website.page.product',$params);
    }

    //ATTENZIONE questa è l'url EMERGENZA scheda abbinamento viene chiamata solo nel caso la categoria non abbia la url impostata nel db
    protected function pairing_details(Request $request)
    {
        $pairing = Pairing::find($request->id);
        if(!$pairing)
        {
            $this->page_404();
        }

        //le macrocategorie per il menu nella colonna a sinistra
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $seo = $this->getSeoGenerico('App\Model\Pairing', $pairing->{'nome_'.\App::getLocale()});

        $pairing_correlati = Pairing::where('product1_id',$pairing->product1_id)->orWhere('product2_id',$pairing->product2_id)->get();
        $pairing_correlati = $pairing_correlati->where('id','!=',$pairing->id);

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null,
            'pairing' => $pairing,
            'pairing_correlati' => $pairing_correlati,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view('website.page.pairing',$params);
    }

    protected function ricerca(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $search = $request->get('searchterm',null);
        if(strlen($search) < 4)
        {
            return back()->with('error',trans('msg.inserire_almeno_4_caratteri'));
        }

        $catalogo = collect();

        foreach ($macrocategorie as $macro)
        {
            $products = $macro->products()->where('visibile',1)->where('availability_id','!=',2)->where('products.nome_'.\App::getLocale(),'LIKE','%' . $search . '%')->get();
            if($products)
            {
                foreach ($products as $product)
                {
                    $product = Product::find($product->id_product);
                    $prezzo_vendita = $product->prezzo_vendita();
                    $elem = [
                        'id'=> $product->id,
                        'type'=> 'product',
                        'prezzo'=> $prezzo_vendita,
                        'nome' => $product->{'nome_'.\App::getLocale()},
                        'object'=>$product
                    ];
                    $catalogo->push($elem);
                }
            }


            $pairings = $macro->pairings()->where('visibile',1)->where('pairings.nome_'.\App::getLocale(),'LIKE','%' . $search . '%')->get();
            if($pairings)
            {
                foreach ($pairings as $pairing)
                {
                    $product1 = Product::find($pairing->product1_id);
                    $product2 = Product::find($pairing->product1_id);
                    $prezzo_vendita = $product1->prezzo_vendita() + $product2->prezzo_vendita();
                    $elem = [
                        'id'=> $pairing->id,
                        'type'=> 'pairing',
                        'prezzo'=> $prezzo_vendita,
                        'nome' => $pairing->{'nome_'.\App::getLocale()},
                        'object'=>$pairing
                    ];
                    $catalogo->push($elem);
                }
            }
        }

        $totali = $catalogo->count();
        $list = $catalogo->sortBy('prezzo');

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null,
            'category' => false,
            'totali' => $totali,
            'titolo' => 'Ricerca per '.$search,
            'products' => false,
            'pairings' => false,
            'ordinamento' => false,
            'descrizione_categoria' => '',
            'styles' => false,
            'chess_materials' => false,
            'board_materials' => false,
            'filtro' => false,
            'list' => $list,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view('website.page.ricerca',$params);
    }

    protected function tutti_prodotti(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        //le configurazioni varie del sito web
        $website_config = \Config::get('website_config');

        //quanti prodotti per pagina
        $per_page = $website_config['num_prod_per_page'];

        $catalogo = collect();

        foreach ($macrocategorie as $macro)
        {
            $products = $macro->products()->where('visibile',1)->where('availability_id','!=',2)->get();
            if($products)
            {
                foreach ($products as $product)
                {
                    $product = Product::find($product->id_product);
                    $prezzo_vendita = $product->prezzo_vendita();
                    $elem = [
                        'id'=> $product->id,
                        'type'=> 'product',
                        'prezzo'=> $prezzo_vendita,
                        'nome' => $product->{'nome_'.\App::getLocale()},
                        'object'=>$product
                    ];
                    $catalogo->push($elem);
                }
            }


            $pairings = $macro->pairings()->where('visibile',1)->get();
            if($pairings)
            {
                foreach ($pairings as $pairing)
                {
                    $product1 = Product::find($pairing->product1_id);
                    $product2 = Product::find($pairing->product1_id);
                    $prezzo_vendita = $product1->prezzo_vendita() + $product2->prezzo_vendita();
                    $elem = [
                        'id'=> $pairing->id,
                        'type'=> 'pairing',
                        'prezzo'=> $prezzo_vendita,
                        'nome' => $pairing->{'nome_'.\App::getLocale()},
                        'object'=>$pairing
                    ];
                    $catalogo->push($elem);
                }
            }
        }

        $totali = $catalogo->count();

        //parametro per ORDINAMENTO prodotti  (dalla url o dalla sessione se ajax)
        //false se non settato
        $ordinamento = $request->query('order', false);

        if($ordinamento)
        {
            //inserisco l'ordinamento nella sessione per eventuali chiamate ajax tramite paginatore
            $request->session()->put('order',$ordinamento);
        }
        //se non arriva dalla query string provo dalla sessione
        else
        {
            $ordinamento = $request->session()->get('order',false);
        }


        switch ($ordinamento)
        {
            //per prezzo crescente
            case 'prezzo|ASC':
                $list = $catalogo->sortBy('prezzo')
                    ->paginate($per_page);
                break;
            //per prezzo decrescente
            case 'prezzo|DESC':
                $list = $catalogo->sortByDesc('prezzo')
                    ->paginate($per_page);
                break;
            //per nome
            case 'nome|ASC':
                $nome = 'nome_'.\App::getLocale();
                $list = $catalogo->sortBy('nome')
                    ->paginate($per_page);
                break;
            default:
                $list = $catalogo->sortBy('prezzo')
                    ->paginate($per_page);
        }

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategory' => false,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null,
            'category' => false,
            'totali' => $totali,
            'titolo' => 'Tutti i prodotti',
            'products' => false,
            'pairings' => false,
            'ordinamento' => $ordinamento,
            'descrizione_categoria' => '',
            'styles' => false,
            'chess_materials' => false,
            'board_materials' => false,
            'filtro' => false,
            'list' => $list,
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];

        return view('website.page.tutti_prodotti',$params);
    }

    protected function azienda(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.azienda',$params);
    }

    protected function dove_siamo(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.dove_siamo',$params);
    }

    protected function contatti(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'form_action' => route('invia_formcontatti',app()->getLocale()),
            'form_name' => 'form_contatti',
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.contatti',$params);
    }

    protected function recensioni(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();
        $reviews = Review::where('visibile',1)->orderBy('id','DESC')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'reviews' => $reviews,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.recensioni',$params);
    }

    protected function modalita_pagamento(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.modalita_pagamento',$params);
    }

    protected function modalita_spedizione(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.modalita_spedizione',$params);
    }

    protected function informativa(Request $request,$url)
    {
        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.informativa',$params);
    }

    protected function account()
    {
        if(!\Auth::check())
        {
            return redirect('/');
        }
        $user = \Auth::getUser();
        $user_details = UserDetail::where('user_id', $user->id)->first();
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'user' => $user,
            'user_details' => $user_details,
            'form_name' => 'form_modifica_account',
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.account',$params);
    }

    protected function orders()
    {
        if(!\Auth::check())
        {
            return redirect('/');
        }
        $user = \Auth::getUser();
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();
        $orders = Order::where('user_id',$user->id)->get();

        $params = [
            'carts' => $this->getCarts(),
            'orders' => $orders,
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.orders',$params);
    }

    protected function wishlist(Request $request,$url)
    {
        //l'utente deve essere loggato altrimenti vedrà un messaggio di alert
        if(\Auth::check())
        {
            $user = \Auth::getUser();
            $wishes = Wish::where('user_id',$user->id)->get();
        }
        else
        {
            $wishes = false;
        }

        $seo = $url->seo;
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $products = [];
        if($wishes && count($wishes) > 0)
        {
            foreach($wishes as $wish)
            {
                if($wish->product)
                {
                    $products[] = $wish->product;
                }
            }
        }


        $params = [
            'carts' => $this->getCarts(),
            'seo' => $seo,
            'wishes' => $wishes,
            'products' => $products,
            'macrocategorie' => $macrocategorie,
            'titolo' => 'Wishlist',
            'is_wishlist' => true, //serve per far comparire i pulsanti rimuovi dalla wishlist e aggiungi al carrello nel box prodotto
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.wishlist',$params);
    }

    protected function wishlist_addpairing(Request $request)
    {
        $pairing_id = $request->id;

        //se non è loggato esco
        if(!\Auth::check())
        {
            return ['result' => 0, 'msg' => trans('msg.per_poter_usufruire_della_wishlist_devi_effettuare_il_login')];
        }

        $user = \Auth::getUser();

        $pairing = Pairing::find($pairing_id);
        if(!$pairing)
        {
            return ['result' => 0, 'msg' => trans('msg.prodotto_non_piu_disponibile')];
        }

        $product1 = $pairing->product1;
        $product2 = $pairing->product2;

        //controllo che non sia già presente nella lista dei desideri il prodotto 1
        $old_wish1 = Wish::where('user_id',$user->id)->where('product_id',$product1->id)->first();
        //controllo che non sia già presente nella lista dei desideri il prodotto 2
        $old_wish2 = Wish::where('user_id',$user->id)->where('product_id',$product2->id)->first();

        //se sono già presenti entrambi
        if($old_wish1 && $old_wish2)
        {
            return ['result' => 0, 'msg' => trans('msg.prodotto_gia_presente_nella_lista_dei_desideri')];
        }

        //se non è già presente il prodotto 1 lo inserisco
        if(!$old_wish1)
        {
            try{
                $wish = new Wish();
                $wish->product_id = $product1->id;
                $wish->user_id = $user->id;
                $wish->save();
            }
            catch(\Exception $e)
            {
                \Log::error($e->getMessage());
                return ['result' => 0, 'msg' => trans('msg.impossibile_eseguire_operazione')];

            }
        }

        //se non è già presente il prodotto 2 lo inserisco
        if(!$old_wish2)
        {
            try{
                $wish = new Wish();
                $wish->product_id = $product2->id;
                $wish->user_id = $user->id;
                $wish->save();
            }
            catch(\Exception $e)
            {
                \Log::error($e->getMessage());
                return ['result' => 0, 'msg' => trans('msg.impossibile_eseguire_operazione')];

            }
        }

        return ['result' => 1, 'msg' => trans('msg.aggiunto_alla_lista_dei_desideri')];


    }

    protected function wishlist_addproduct(Request $request)
    {
        $product_id = $request->id;

        //se non è loggato esco
        if(!\Auth::check())
        {
            return ['result' => 0, 'msg' => trans('msg.per_poter_usufruire_della_wishlist_devi_effettuare_il_login')];
        }

        $user = \Auth::getUser();

        $product = Product::find($product_id);
        if(!$product)
        {
            return ['result' => 0, 'msg' => trans('msg.prodotto_non_piu_disponibile')];
        }

        //controllo che non sia già presente nella lista dei desideri
        $old_wish = Wish::where('user_id',$user->id)->where('product_id',$product->id)->first();
        if($old_wish)
        {
            return ['result' => 0, 'msg' => trans('msg.prodotto_gia_presente_nella_lista_dei_desideri')];
        }

        try{
            $wish = new Wish();
            $wish->product_id = $product->id;
            $wish->user_id = $user->id;
            $wish->save();

            return ['result' => 1, 'msg' => trans('msg.aggiunto_alla_lista_dei_desideri')];
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());
            return ['result' => 0, 'msg' => trans('msg.impossibile_eseguire_operazione')];

        }
    }

    protected function clear_cookies()
    {
        $_POST = array_map("trim", $_POST); // se esternamente ad uno switch
        $expiration_date = time() + (10 * 365 * 24 * 60 * 60); // in 10 years => Faccio scadere il cookie in un futuro abbastanza lontano
        setcookie("c_acceptance", "yes", $expiration_date, "/");
    }

    protected function cookies_policy()
    {
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'carts' => $this->getCarts(),
            'macrocategorie' => $macrocategorie,
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.page.cookies_policy',$params);
    }

    protected function wishlist_delete(Request $request)
    {
        //se non è loggato esco
        if(!\Auth::check())
        {
            return ['result' => 0, 'msg' => trans('msg.per_poter_usufruire_della_wishlist_devi_effettuare_il_login')];
        }

        $product_id = $request->id;
        $wish = Wish::where('user_id',\Auth::user()->id)->where('product_id',$product_id)->first();

        $wish->delete();

        return ['result' => 0, 'msg' => trans('msg.prodotto_eliminato_dalla_tua_lista_desideri')];
    }

    public function add_to_newsletter(Request $request)
    {
        $request->validate([
            'news_email'    => 'required|email'
        ]);

        $email = $request->news_email;

        //vedo se già iscritto
        $old_email = NewsletterSubscriber::where('email',$email)->first();
        if($old_email)
        {
            return back()->with('error',trans('msg.questa_email_risulta_essere_gia_iscritta'));
        }

        try{
            $iscriber = new NewsletterSubscriber();
            $iscriber->email = $email;
            $iscriber->lang = \App::getLocale();
            $iscriber->save();
        }
        catch(\Exception $e)
        {
            return back()->with('error',trans('msg.errore'));
        }

        return back()->with('success',trans('msg.iscrizione_avvenuta_con_successo'));

    }

    public function invia_formcontatti(Request $request)
    {
        $data = $request->post();
        $config = \Config::get('website_config');
        $secret = $config['recaptcha_secret'];

        if(!GoogleRecaptcha::verifyGoogleRecaptcha($data,$secret))
        {
            return ['result' => 0, 'msg' => trans('msg.il_codice_di_controllo_errato')];
        }

        $to = ($config['in_sviluppo']) ? $config['email_debug'] : $config['email'];

        $mail = new Contact($data);

        try{
            \Mail::to($to)->send($mail);
        }
        catch(\Exception $e)
        {
            return ['result' => 0, 'msg' => $e->getMessage()];
        }

        return ['result' => 1, 'msg' => trans('msg.grazie_per_averci_contattato')];

    }

    private function getOrdinamentoParam(Request $request)
    {
        if(!$request->ajax())
        {
            //se NO AJAX lo prendo dalla query string dell url
            $ordinamento = $request->query('order', false);
        }
        else
        {
            //se AJAX il parametro dell'ordinamento lo prendo dalla sessione
            $ordinamento = $request->session()->get('order',false);
        }
        return $ordinamento;
    }

    private function getFiltroParam(Request $request)
    {
        if(!$request->ajax())
        {
            //se NO AJAX lo prendo dalla query string dell url
            $filtro = $request->query('filter',false);
        }
        else
        {
            //se AJAX il parametro del filtro lo prendo dalla sessione
            $filtro = $request->session()->get('filter',false);
        }
        return $filtro;
    }

    private function getPageParam(Request $request, $model=null)
    {
        if(!$request->ajax())
        {
            //se NO AJAX lo prendo dalla query string dell url
            if($model instanceof Category)
            {
                $page = $request->session()->get('page_cat_'.$model->id,false);
            }
            elseif($model instanceof Macrocategory)
            {
                $page = $request->session()->get('page_mac_'.$model->id,false);
            }
            else
            {
                $page = $request->session()->get('page',false);
            }
        }
        else
        {
            //se AJAX il parametro PAGE lo prendo dalla url
            $page = $request->get('page');
        }
        return $page;
    }

    private function setParamsInSession(Request $request,$ordinamento,$filtro,$page,$model=null)
    {
        if(!$request->ajax())
        {
            //inserisco l'ordinamento nella sessione per eventuali chiamate ajax tramite paginatore
            $request->session()->put('order',$ordinamento);

            //inserisco il filtro nella sessione per eventuali chiamate ajax tramite paginatore
            $request->session()->put('filter',$filtro);
        }
        else
        {
            //inserisco il parametro page nella sessione
            if($model instanceof Category)
            {
                $request->session()->put('page_cat_'.$model->id,$page);
            }
            elseif($model instanceof Macrocategory)
            {
                $request->session()->put('page_mac_'.$model->id,$page);
            }
            else
            {
                $request->session()->put('page',$page);
            }
        }
    }

    /**
     * @param Request $request
     * @param $page
     * setta il Paginatore ad una determita pagina (utile per il torna indietro del browser)
     */
    private function updatePaginator(Request $request,$page)
    {
        if(session()->previousUrl() == $request->fullUrl())
        {
            if($page)
            {
                Paginator::currentPageResolver(function () use ($page) {
                    return $page;
                });
            }
        }
    }

    /**
     * @param $model
     * @param $filtro
     * @return mixed
     * Funzione per sapere il numero totale dei prodotti di una categoria o macrocategoria
     */
    private function getTotalPairings($model,$filtro)
    {
        if($filtro)
        {
            $filtro_arr = explode("|",$filtro);
            $tipo_filtro = $filtro_arr[0];
            $parametro_filtro = $filtro_arr[1];

            switch($tipo_filtro)
            {
                case 'style':
                    $totali = $model->pairings()->where('visibile',1)->where('style_id',$parametro_filtro)->count();
                    break;
                case 'material_chess':
                    $totali = $model->pairings_for_list($tipo_filtro,$parametro_filtro)->count();
                    break;
                case 'material_board':
                    $totali = $model->pairings_for_list($tipo_filtro,$parametro_filtro)->count();
                    break;
                default:
                    $totali = $model->pairings_for_list()->count();
            }
        }
        else
        {
            $totali = $model->pairings()->where('visibile',1)->count();
        }

        return $totali;
    }

    /**
     * @param $model
     * @param $filtro
     * @param $ordinamento
     * @param $per_page
     * @return mixed
     * Funzione per ottenere tutti gli abbinamenti in base ai filtri e all'ordinamento stabiliti e per paginatore
     */
    private function getPairings($model, $filtro, $ordinamento, $per_page)
    {
        //Se Presente il parametro filtro allora prendo gli abbinamenti in base al filtro
        if($filtro)
        {
            $filtro_arr = explode("|",$filtro);
            $tipo_filtro = $filtro_arr[0];
            $parametro_filtro = $filtro_arr[1];

            switch($tipo_filtro)
            {
                //per stile
                case 'style':
                    $pairings = $model->pairings_for_list()
                        ->sortBy('prezzo')
                        ->where('style_id',$parametro_filtro)
                        ->paginate($per_page);
                    break;
                //per materiale scacchi
                case 'material_chess':
                    $pairings = $model->pairings_for_list($tipo_filtro,$parametro_filtro)
                        ->sortBy('prezzo')
                        ->paginate($per_page);
                    break;
                //per materiale scacchiera
                case 'material_board':
                    $pairings = $model->pairings_for_list($tipo_filtro,$parametro_filtro)
                        ->sortBy('prezzo')
                        ->paginate($per_page);
                    break;
                default:
                    $pairings = $model->pairings_for_list()
                        ->sortBy('prezzo')
                        ->paginate($per_page);
            }
        }
        //altrimenti prendo i prodotti in base all'ordinamento
        else
        {
            //ATTENZIONE!! in questo caso usiamo sortBy perchè il risultato è una collection e non un eloquent array
            switch ($ordinamento)
            {
                //per prezzo crescente
                case 'prezzo|ASC':
                    $pairings = $model->pairings_for_list()
                        ->sortBy('prezzo')
                        ->paginate($per_page);
                    break;
                //per prezzo decrescente
                case 'prezzo|DESC':
                    $pairings = $model->pairings_for_list()
                        ->sortByDesc('prezzo')
                        ->paginate($per_page);
                    break;
                //per codice
                case 'codice|ASC':
                    $nome = 'nome_'.\App::getLocale();
                    $pairings = $model->pairings_for_list()
                        ->sortBy($nome)
                        ->paginate($per_page);
                    break;
                default:
                    $pairings = $model->pairings_for_list()
                        ->sortBy('prezzo')
                        ->paginate($per_page);
            }
        }
        return $pairings;
    }

    /**
     * @param $model
     * @return mixed
     * Funzione per sapere il numero totale di una categoria o macrocategoria
     */
    private function getTotalProducts($model)
    {
        return $model->products()->where('visibile',1)->where('availability_id','!=',2)->count();
    }

    private function getProducts($model,$ordinamento,$per_page)
    {
        switch ($ordinamento)
        {
            case 'prezzo|ASC':
                $products = $model->products()
                    ->where('visibile',1)
                    ->where('availability_id','!=',2)
                    ->orderBy('minimal','ASC')
                    ->paginate($per_page);
                break;
            case 'prezzo|DESC':
                $products = $model->products()
                    ->where('visibile',1)
                    ->where('availability_id','!=',2)
                    ->orderBy('minimal','DESC')
                    ->paginate($per_page);
                break;
            case 'nome|ASC':
                $nome = 'nome_'.\App::getLocale();
                $products = $model->products()
                    ->where('visibile',1)
                    ->where('availability_id','!=',2)
                    ->orderBy('products.'.$nome,'ASC')
                    ->paginate($per_page);
                break;
            default:
                $products = $model->products()
                    ->where('visibile',1)
                    ->where('availability_id','!=',2)
                    ->orderBy('minimal')
                    ->paginate($per_page);
        }

        return $products;
    }

    private function getCarts()
    {
        if(\Auth::check())
        {
            $user = \Auth::getUser();
            $carts = Cart::where('user_id',$user->id)->get();
        }
        else
        {
            $carts = Cart::where('session_id',session()->getId())->get();
        }

        //rimuovo i prodotti che eventualmente non esistono più
        foreach($carts as $key=>$cart)
        {
            if(!$cart->product)
            {
                $cart->delete();
                $carts->forget($key);
            }
        }
        return $carts;
    }

    private function getSeoGenerico($model_type,$segnaposto)
    {
        $seo = Seo::where('bind_to',$model_type)->where('locale',\App::getLocale())->first();
        $seo->title = str_replace("%s",$segnaposto ,$seo->title);
        $seo->h1 = str_replace("%s",$segnaposto ,$seo->h1);
        $seo->description = str_replace("%s",$segnaposto ,$seo->description);
        $seo->h2 = str_replace("%s",$segnaposto ,$seo->h2);
        $seo->alt = str_replace("%s",$segnaposto ,$seo->alt);
        return $seo;
    }
}
