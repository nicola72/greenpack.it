<?php

namespace App\Http\Controllers\Cms;

use App\Model\Domain;
use App\Model\File;
use App\Model\Module;
use App\Model\ModuleConfig;
use App\Model\Newsitem;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $newsitems = Newsitem::all();
        $params = [
            'title_page' => 'News',
            'newsitems' => $newsitems
        ];

        return view('cms.news.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $params = [
            'form_name' => 'form_create_news',
        ];
        return view('cms.news.create',$params);
    }

    public function store(Request $request)
    {
        $langs = \Config::get('langs');

        try{
            $item = new Newsitem();
            foreach ($langs as $lang)
            {
                $item->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $item->{'desc_'.$lang} = $request->{'desc_'.$lang};
            }
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/news');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
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
        $item = Newsitem::find($id);
        $params = [
            'item' => $item,
            'form_name' => 'form_edit_news'
        ];

        return view('cms.news.edit',$params);
    }

    public function update(Request $request, $id)
    {
        $item = Newsitem::find($id);

        $langs = \Config::get('langs');

        try{

            foreach ($langs as $lang)
            {
                $item->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $item->{'desc_'.$lang} = $request->{'desc_'.$lang};
            }
            $item->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.news');
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => $url];
    }

    public function images(Request $request, $id)
    {
        $item = Newsitem::find($id);

        $images = File::where('fileable_id',$id)->where('fileable_type','App\Model\Newsitem')->orderBy('order')->get();

        //prendo il file di configurazione del modulo Product
        $productModule = Module::where('nome','news')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$productModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $file_restanti = $upload_config->max_numero_file - $images->count();
        $limit_max_file = ($file_restanti > 0) ? false : true;

        $params = [
            'title_page' => 'Immagini News '.$item->nome_it,
            'images' => $images,
            'item' => $item,
            'limit_max_file' =>$limit_max_file,
            'max_numero_file'=> $upload_config->max_numero_file,
            'max_file_size' => $upload_config->max_file_size,
            'file_restanti' => $file_restanti,
            'extensions'=>$upload_config->extensions,

        ];
        return view('cms.news.images',$params);
    }

    public function upload_images(Request $request)
    {
        //prendo il file di configurazione del modulo Product
        $productModule = Module::where('nome','news')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$productModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $fileable_id = $request->fileable_id;
        $fileable_type = 'App\Model\Newsitem';

        $uploadedFile = $request->file('file');
        $filename = time().$uploadedFile->getClientOriginalName();

        try{
            \Storage::disk('news')->putFileAs('', $uploadedFile, $filename);
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        //se CROP configurato
        if(isset($upload_config->crop) && $upload_config->crop)
        {
            $x = $upload_config->default_crop_x;
            $y = $upload_config->default_crop_y;
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/news/crop/'.$filename;
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/news/'.$filename);
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
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/news/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/news/small/'.$filename;
            $img->resize($small, null, function ($constraint) {$constraint->aspectRatio();});
            $img->save($path);

            //la big
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/news/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/news/big/'.$filename;
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

        $url = route('cms.news');
        return ['result' => 1,'msg' => 'File caricato con successo!','url' => $url];
    }

    public function destroy($id)
    {
        $news = Newsitem::find($id);
        $news->delete();

        return back()->with('success','Elemento cancellato!');
    }

    public function switch_visibility(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Newsitem::find($id);
            $item->visibile = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function switch_popup(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Newsitem::find($id);
            $item->popup = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }

    public function move_up(Request $request,$id)
    {
        $news = Newsitem::find($id);
        $news->moveOrderUp();
        return back();
    }

    public function move_down(Request $request,$id)
    {
        $news = Newsitem::find($id);
        $news->moveOrderDown();
        return back();
    }
}
