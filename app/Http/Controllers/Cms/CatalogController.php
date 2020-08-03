<?php
namespace App\Http\Controllers\Cms;

use App\Model\Catalog;
use App\Model\File;
use App\Model\Module;
use App\Model\ModuleConfig;
use App\Model\Newsitem;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cataloghi = Catalog::all();
        $params = [
            'title_page' => 'Cataloghi',
            'cataloghi' => $cataloghi,
        ];
        return view('cms.catalog.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $params = [
            'form_name' => 'form_create_catalog',
        ];
        return view('cms.catalog.create',$params);
    }


    public function store(Request $request)
    {
        $langs = \Config::get('langs');

        try{
            $catalog = new Catalog();
            foreach ($langs as $lang)
            {
                $catalog->{'nome_'.$lang} = $request->{'nome_'.$lang};
            }
            $catalog->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/catalog');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    public function move_up(Request $request,$id)
    {
        $cat = Catalog::find($id);
        $cat->moveOrderUp();
        return back();
    }

    public function move_down(Request $request,$id)
    {
        $cat = Catalog::find($id);
        $cat->moveOrderDown();
        return back();
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
        $catalogo = Catalog::find($id);
        $params = [
            'catalogo' => $catalogo,
            'form_name' => 'form_edit_catalogo'
        ];

        return view('cms.catalog.edit',$params);
    }


    public function update(Request $request, $id)
    {
        $catalog = Catalog::find($id);

        $langs = \Config::get('langs');

        try{

            foreach ($langs as $lang)
            {
                $catalog->{'nome_'.$lang} = $request->{'nome_'.$lang};
            }
            $catalog->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.cataloghi');
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
        //return back()->with('error','Devo fare controllo prodotti presenti!');
        $catalog = Catalog::find($id);
        $catalog->delete();

        return back()->with('success','Elemento cancellato!');
    }

    public function switch_visibility(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Catalog::find($id);
            $item->visibile = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function pdf(Request $request, $id)
    {
        $item = Catalog::find($id);
        $pdfs = File::where('fileable_id',$id)->where('fileable_type','App\Model\Catalog')->where('type',2)->orderBy('order')->get();

        //prendo il file di configurazione del modulo Catalog
        $catalogModule = Module::where('nome','cataloghi')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$catalogModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_pdf')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $file_restanti = $upload_config->max_numero_file - $pdfs->count();
        $limit_max_file = ($file_restanti > 0) ? false : true;

        $params = [
            'title_page' => 'Pdf Catalogo '.$item->nome_it,
            'pdfs' => $pdfs,
            'item' => $item,
            'limit_max_file' =>$limit_max_file,
            'max_numero_file'=> $upload_config->max_numero_file,
            'max_file_size' => $upload_config->max_file_size,
            'file_restanti' => $file_restanti,
            'extensions'=>$upload_config->extensions,

        ];
        return view('cms.catalog.pdfs',$params);
    }

    public function upload_pdf(Request $request)
    {
        //prendo il file di configurazione del modulo Catalog
        $catalogModule = Module::where('nome','cataloghi')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$catalogModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_pdf')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $fileable_id = $request->fileable_id;
        $fileable_type = 'App\Model\Catalog';
        $type = 2;

        $uploadedFile = $request->file('file');
        $filename = time().$uploadedFile->getClientOriginalName();

        try{
            \Storage::disk('catalog')->putFileAs('', $uploadedFile, $filename);
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }


        //inserisco il nome del file nel db
        try{
            $file = new File();
            $file->path = $filename;
            $file->type = $type;
            $file->fileable_id = $fileable_id;
            $file->fileable_type = $fileable_type;
            $file->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        //---//

        $url = route('cms.cataloghi');
        return ['result' => 1,'msg' => 'File caricato con successo!','url' => $url];
    }

    public function images(Request $request, $id)
    {
        $item = Catalog::find($id);

        $images = File::where('fileable_id',$id)->where('fileable_type','App\Model\Catalog')->where('type',1)->orderBy('order')->get();

        //prendo il file di configurazione del modulo Catalog
        $catalogModule = Module::where('nome','cataloghi')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$catalogModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $file_restanti = $upload_config->max_numero_file - $images->count();
        $limit_max_file = ($file_restanti > 0) ? false : true;

        $params = [
            'title_page' => 'Immagine Catalogo '.$item->nome_it,
            'images' => $images,
            'item' => $item,
            'limit_max_file' =>$limit_max_file,
            'max_numero_file'=> $upload_config->max_numero_file,
            'max_file_size' => $upload_config->max_file_size,
            'file_restanti' => $file_restanti,
            'extensions'=>$upload_config->extensions,

        ];
        return view('cms.catalog.images',$params);
    }

    public function upload_images(Request $request)
    {
        //prendo il file di configurazione del modulo Catalog
        $catalogModule = Module::where('nome','cataloghi')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$catalogModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $fileable_id = $request->fileable_id;
        $fileable_type = 'App\Model\Catalog';

        $uploadedFile = $request->file('file');
        $filename = time().$uploadedFile->getClientOriginalName();

        try{
            \Storage::disk('catalog')->putFileAs('', $uploadedFile, $filename);
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        //se CROP configurato
        if(isset($upload_config->crop) && $upload_config->crop)
        {
            $x = $upload_config->default_crop_x;
            $y = $upload_config->default_crop_y;
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/catalog/crop/'.$filename;
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/catalog/'.$filename);
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
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/catalog/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/catalog/small/'.$filename;
            $img->resize($small, null, function ($constraint) {$constraint->aspectRatio();});
            $img->save($path);

            //la big
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/catalog/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/catalog/big/'.$filename;
            $img->resize($big, null, function ($constraint) {$constraint->aspectRatio();});
            $img->save($path);

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

        $url = route('cms.cataloghi');
        return ['result' => 1,'msg' => 'File caricato con successo!','url' => $url];
    }
}