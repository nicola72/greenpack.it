<?php

namespace App\Http\Controllers\Cms;

use App\Model\Availability;
use App\Model\Category;
use App\Model\Domain;
use App\Model\File;
use App\Model\Module;
use App\Model\ModuleConfig;
use App\Model\Product;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        $params = [
            'title_page' => 'Prodotti',
            'products' => $products,
        ];
        return view('cms.product.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorie = Category::all()->sortBy('nome_it');
        $availabilities = Availability::all();
        $params = [
            'form_name' => 'form_create_product',
            'title_page'=> 'Nuovo Prodotto',
            'categorie' => $categorie,
            'availabilities' => $availabilities,
        ];
        return view('cms.product.create',$params);
    }


    public function store(Request $request)
    {
        $langs = \Config::get('langs');
        $prezzo_scontato = ($request->prezzo_scontato != '') ? $request->prezzo_scontato : 0;
        $prezzo_fabbrica = ($request->prezzo_fabbrica != '') ? $request->prezzo_fabbrica : 0;

        try{
            $product = new Product();
            $product->category_id = $request->category_id;
            $product->availability_id = $request->availability_id;
            $product->codice = $request->codice;
            $product->prezzo = str_replace(',','.',$request->prezzo);
            $product->prezzo_scontato = str_replace(',','.',$prezzo_scontato);
            $product->prezzo_fabbrica = str_replace(',','.',$prezzo_fabbrica);
            $product->acquistabile = $request->acquistabile;
            $product->acquistabile_italfama = $request->acquistabile_italfama;
            $product->peso = $request->peso;
            $product->stock = $request->stock;
            $product->novita = $request->novita;
            $product->offerta = $request->offerta;
            $product->visibile = $request->visibile;
            $product->italfama = $request->italfama;
            foreach ($langs as $lang)
            {
                $product->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $product->{'desc_'.$lang} = $request->{'desc_'.$lang};
                $product->{'desc_breve_'.$lang} = $request->{'desc_breve_'.$lang};
                $product->{'misure_'.$lang} = $request->{'misure_'.$lang};

            }
            $product->save();
            $product_id = $product->id;

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        //1# Creo una url di default per ogni lingua
        foreach ($langs as $lang)
        {
            $domain = Domain::where('locale',$lang)->first();
            try{
                $url = new Url();
                $url->domain_id = $domain->id;
                $url->locale = $lang;
                $url->slug = trans('msg.dettaglio',[],$lang).'-'.$product_id;
                $url->urlable_id = $product_id;
                $url->urlable_type = 'App\Model\Product';
                $url->save();
            }
            catch(\Exception $e)
            {
                return ['result' => 0,'msg' => $e->getMessage()];
            }
        }
        //1# Fine

        $url = route('cms.prodotti');
        return ['result' => 1,'msg' => 'Elemento creato con successo!','url' => $url];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categorie = Category::all();
        $availabilities = Availability::all();

        $product = Product::find($id);
        $params = [
            'title_page' => 'Modifica Prodotto '.$product->codice,
            'product' => $product,
            'categorie' => $categorie,
            'availabilities' => $availabilities,
            'form_name' => 'form_edit_product'
        ];

        return view('cms.product.edit',$params);
    }


    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $prezzo_scontato = ($request->prezzo_scontato != '') ? $request->prezzo_scontato : 0;

        $langs = \Config::get('langs');

        try{

            $product->category_id = $request->category_id;
            $product->availability_id = $request->availability_id;
            $product->codice = $request->codice;
            $product->prezzo = str_replace(',','.',$request->prezzo);
            $product->prezzo_scontato = str_replace(',','.',$prezzo_scontato);
            $product->prezzo_fabbrica = str_replace(',','.',$request->prezzo_fabbrica);
            $product->acquistabile = $request->acquistabile;
            $product->acquistabile_italfama = $request->acquistabile_italfama;
            $product->peso = $request->peso;
            $product->stock = $request->stock;
            foreach ($langs as $lang)
            {
                $product->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $product->{'desc_'.$lang} = $request->{'desc_'.$lang};
                $product->{'desc_breve_'.$lang} = $request->{'desc_breve_'.$lang};
                $product->{'misure_'.$lang} = $request->{'misure_'.$lang};
            }
            $product->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.prodotti');
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => $url];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();

        //elimino anche le url associate al prodotto
        $urls = Url::where('urlable_id',$product->id)->where('urlable_type','App\Model\Product')->get();
        foreach ($urls as $url)
        {
            $url->delete();
        }

        //elimino anche i file associati al prodotto
        $files = File::where('fileable_id',$product->id)->where('fileable_type','App\Model\Product')->get();
        foreach ($files as $file)
        {
            $file->delete();
        }

        return back()->with('success','Elemento cancellato!');
    }

    public function images(Request $request, $id)
    {
        $product = Product::find($id);

        $images = File::where('fileable_id',$id)->where('fileable_type','App\Model\Product')->orderBy('order')->get();

        //prendo il file di configurazione del modulo Product
        $productModule = Module::where('nome','prodotti')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$productModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $file_restanti = $upload_config->max_numero_file - $images->count();
        $limit_max_file = ($file_restanti > 0) ? false : true;

        $params = [
            'title_page' => 'Immagini Prodotto '.$product->codice,
            'images' => $images,
            'product' => $product,
            'limit_max_file' =>$limit_max_file,
            'max_numero_file'=> $upload_config->max_numero_file,
            'max_file_size' => $upload_config->max_file_size,
            'file_restanti' => $file_restanti,
            'extensions'=>$upload_config->extensions,

        ];
        return view('cms.product.images',$params);
    }

    public function upload_images(Request $request)
    {
        //prendo il file di configurazione del modulo Product
        $productModule = Module::where('nome','prodotti')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$productModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $fileable_id = $request->fileable_id;
        $fileable_type = 'App\Model\Product';

        $uploadedFile = $request->file('file');
        $filename = time().$uploadedFile->getClientOriginalName();

        try{
            \Storage::disk('file')->putFileAs('', $uploadedFile, $filename);
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        //se CROP configurato
        if(isset($upload_config->crop) && $upload_config->crop)
        {
            $x = $upload_config->default_crop_x;
            $y = $upload_config->default_crop_y;
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/crop/'.$filename;
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/'.$filename);
            $img->crop($x, $y);
            $img->save($path);
        }
        //---//

        //se configurate RESIZE
        if(isset($upload_config->resize))
        {
            $resizes = explode(',',$upload_config->resize);

            //faccio 2 resize come il vecchio sito e le chiamo big e small
            $small = $resizes[0];
            $big = $resizes[1];

            //la small
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/small/'.$filename;
            $img->resize($small, null, function ($constraint) {$constraint->aspectRatio();});
            $img->save($path);

            //la big
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/big/'.$filename;
            $img->resize($big, null, function ($constraint) {$constraint->aspectRatio();});
            $img->save($path);

            //creo anche le watermarks per chess
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/small/'.$filename);
            $img->insert($_SERVER['DOCUMENT_ROOT'].'/img/watermark2_small.png', 'bottom-right', 50, 50);
            $img->save($_SERVER['DOCUMENT_ROOT'].'/file/wmi/small/'.$filename);
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/big/'.$filename);
            $img->insert($_SERVER['DOCUMENT_ROOT'].'/img/watermark2_small.png', 'bottom-right', 50, 50);
            $img->save($_SERVER['DOCUMENT_ROOT'].'/file/wmi/big/'.$filename);

            //crea anche le watermarks per italfama
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/small/'.$filename);
            $img->insert($_SERVER['DOCUMENT_ROOT'].'/img/watermark_small.png', 'bottom-right', 50, 50);
            $img->save($_SERVER['DOCUMENT_ROOT'].'/file/wmc/small/'.$filename);
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/big/'.$filename);
            $img->insert($_SERVER['DOCUMENT_ROOT'].'/img/watermark_small.png', 'bottom-right', 50, 50);
            $img->save($_SERVER['DOCUMENT_ROOT'].'/file/wmc/big/'.$filename);
        }
        //---//

        //inserisco il nome del file nel db
        try{
            $file = new File();
            $file->path = $filename;
            $file->fileable_id = $fileable_id;
            $file->fileable_type = $fileable_type;
            $file->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        //---//

        $url = route('cms.prodotti');
        return ['result' => 1,'msg' => 'File caricato con successo!','url' => $url];
    }

    public function sort_images(Request $request)
    {
        $positions = $request->pos;
        $array_pos = explode(";", $positions);

        foreach ($array_pos as $valore)
        {
            $temp = explode("=", $valore);
            $id = intval($temp[0]);
            $ordine = intval($temp[1]);
            echo 'id='.$id." ordine=".$ordine;
            /*$file = File::find($id);
            $file->order = $ordine;
            $file->save();*/
        }
        return;
    }

    public function switch_visibility(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Product::find($id);
            $item->visibile = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function switch_visibility_italfama(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Product::find($id);
            $item->italfama = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function switch_offerta(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Product::find($id);
            $item->offerta = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function switch_novita(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Product::find($id);
            $item->novita = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }
}
