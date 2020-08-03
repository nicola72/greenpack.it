<?php

namespace App\Http\Controllers\Cms;

use App\Model\Domain;
use App\Model\Macrocategory;
use App\Model\Module;
use App\Model\ModuleConfig;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class MacrocategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$macros = Macrocategory::all()->sortBy('order');
        $macros = Macrocategory::orderBy('order', 'desc')->get();
        $params = [
            'title_page' => 'Categorie Principali',
            'macros' => $macros,
        ];
        return view('cms.macrocategory.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $params = [
            'form_name' => 'form_create_macro'
        ];
        return view('cms.macrocategory.create',$params);
    }

    public function store(Request $request)
    {
        $langs = \Config::get('langs');

        try{
            $macro = new Macrocategory();
            foreach ($langs as $lang)
            {
                $macro->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $macro->{'desc_'.$lang} = $request->{'desc_'.$lang};
            }
            $macro->save();
            $macro_id = $macro->id;

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
                //$url->slug = strtolower(str_replace(" ","_", $request->{'nome_'.$lang}));
                $url->slug = Str::slug( $request->{'nome_'.$lang}, '-');
                $url->urlable_id = $macro_id;
                $url->urlable_type = 'App\Model\Macrocategory';
                $url->save();
            }
            catch(\Exception $e)
            {
                return ['result' => 0,'msg' => $e->getMessage()];
            }
        }
        //1# Fine

        $url = url('/cms/macrocategory');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    public function move_up(Request $request,$id)
    {
        $macro = Macrocategory::find($id);
        $macro->moveOrderUp();
        return back();
    }

    public function move_down(Request $request,$id)
    {
        $macro = Macrocategory::find($id);
        $macro->moveOrderDown();
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
        $macro = Macrocategory::find($id);
        $params = [
            'macro' => $macro,
            'form_name' => 'form_edit_macro'
        ];

        return view('cms.macrocategory.edit',$params);
    }


    public function update(Request $request, $id)
    {
        $macro = Macrocategory::find($id);

        $langs = \Config::get('langs');

        try{

            foreach ($langs as $lang)
            {
                $macro->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $macro->{'desc_'.$lang} = $request->{'desc_'.$lang};
                $macro->save();
            }

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.macrocategorie');
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
        $macro = Macrocategory::find($id);

        //controllo che non ci siano categorie associate
        $categories = $macro->categories;
        if($categories->isNotEmpty())
        {
            return back()->with('error','Errore! Sono presenti categorie associata a questa Macrocategoria!');
        }

        $macro->delete();
        //elimino anche le url associate alla macro
        $urls = Url::where('urlable_id',$macro->id)->where('urlable_type','App\Model\Macrocategory')->get();
        foreach ($urls as $url)
        {
            $url->delete();
        }

        return back()->with('success','Elemento cancellato!');
    }

    public function switch_stato(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Macrocategory::find($id);
            $item->stato = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }
}
