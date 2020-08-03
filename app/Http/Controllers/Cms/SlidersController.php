<?php

namespace App\Http\Controllers\Cms;

use App\Model\File;
use App\Model\Module;
use App\Model\ModuleConfig;
use App\Model\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;

class SlidersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = Slider::all();
        $params = [
            'title_page' => 'Sliders',
            'sliders' => $sliders,
        ];
        return view('cms.sliders.index',$params);
    }

    public function images(Request $request, $id)
    {
        $slider = Slider::find($id);

        $images = File::where('fileable_id',$id)->where('fileable_type','App\Model\Slider')->orderBy('order')->get();

        //prendo il file di configurazione del modulo Product
        $productModule = Module::where('nome','sliders')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$productModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $file_restanti = $upload_config->max_numero_file - $images->count();
        $limit_max_file = ($file_restanti > 0) ? false : true;

        $params = [
            'title_page' => 'Immagini Slider '.$slider->nome,
            'images' => $images,
            'slider' => $slider,
            'limit_max_file' =>$limit_max_file,
            'max_numero_file'=> $upload_config->max_numero_file,
            'max_file_size' => $upload_config->max_file_size,
            'file_restanti' => $file_restanti,
            'extensions'=>$upload_config->extensions,

        ];
        return view('cms.sliders.images',$params);
    }

    public function upload_images(Request $request)
    {
        //prendo il file di configurazione del modulo Product
        $productModule = Module::where('nome','sliders')->first();
        $moduleConfigs = ModuleConfig::where('module_id',$productModule->id)->get();
        $uploadImgConfig = $moduleConfigs->where('nome','upload_image')->first();
        $upload_config = json_decode($uploadImgConfig->value);
        //---//

        $fileable_id = $request->fileable_id;
        $fileable_type = 'App\Model\Slider';

        $uploadedFile = $request->file('file');
        $filename = time().$uploadedFile->getClientOriginalName();
        //$filename = $uploadedFile->getClientOriginalName();

        try{
            \Storage::disk('sliders')->putFileAs('', $uploadedFile, $filename);
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        //se configurate RESIZE
        if(isset($upload_config->resize))
        {
            $resizes = explode(',',$upload_config->resize);

            //faccio 1 resize come il vecchio sito e le chiamo big e small
            $big = $resizes[0];

            //la big
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/sliders/'.$filename);
            $path = $_SERVER['DOCUMENT_ROOT'].'/file/sliders/big/'.$filename;
            $img->resize($big, null, function ($constraint) {$constraint->aspectRatio();});
            $img->save($path);


            //creo anche le watermarks per chess
            $img = Image::make($_SERVER['DOCUMENT_ROOT'].'/file/sliders/big/'.$filename);
            $img->insert($_SERVER['DOCUMENT_ROOT'].'/img/watermark2.png', 'bottom-right', 50, 50);
            $img->save($_SERVER['DOCUMENT_ROOT'].'/file/sliders/wmi/big/'.$filename);

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

        $url = route('cms.sliders');
        return ['result' => 1,'msg' => 'File caricato con successo!','url' => $url];
    }

    public function switch_visibility(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        $sliders = Slider::all();
        foreach($sliders as $slider)
        {
            $slider->visibile = 0;
            $slider->save();
        }

        try{
            $item = Slider::find($id);
            $item->visibile = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];
    }
}
