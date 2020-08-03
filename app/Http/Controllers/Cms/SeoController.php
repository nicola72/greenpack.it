<?php

namespace App\Http\Controllers\Cms;

use App\Model\Seo;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SeoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seos = Seo::all();

        $params = [
            'seos' => $seos,
            'title_page' => 'Seo'
        ];

        return view('cms.seo.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $params = [
            'form_name' => 'form_create_seo',
            'title_page'=> 'Nuovo Seo',
        ];
        return view('cms.seo.create',$params);
    }


    public function store(Request $request)
    {
        try{
            $seo = new Seo();
            $seo->locale = $request->lang;
            $seo->title = $request->title;
            $seo->description = $request->description;
            $seo->h1 = $request->h1;
            $seo->h2 = $request->h2;
            $seo->alt = $request->alt;
            $seo->keywords = $request->keywords;
            $seo->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.seo');
        return ['result' => 1,'msg' => 'Elemento creato con successo!','url' => $url];
    }

    public function associa_url(Request $request,$id)
    {
        $seo = Seo::find($id);
        $urlable_types = [
            'App\Model\Page',
            'App\Model\Macrocategory',
            'App\Model\Category',
            'App\Model\Product',
            'App\Model\Pairing'
        ];
        $params = [
            'urlable_types' => $urlable_types,
            'seo'=> $seo,
            'form_name' => 'form_associa_url',
        ];
        return view('cms.seo.associa_url',$params);
    }

    public function get_urls_by_type(Request $request)
    {
        $url_type = $request->url_type;
        $urls = Url::where('urlable_type',$url_type)->orderBy('locale')->get();
        $html = '';
        if(count($urls) > 0)
        {
            foreach ($urls as $url)
            {
                $url_completa = "www".$url->domain->nome."/".$url->locale."/".$url->slug;
                $html.= '<option value="'.$url->id.'">'.$url_completa.'</option>';
            }
        }

        echo $html;
    }

    public function store_associazione_url(Request $request)
    {
        $seo_id = $request->seo_id;
        $url_id = $request->url_id;

        //controllo che questo seo non sia già associato ad un url
        $altra_url = Url::where('seo_id',$seo_id)->first();
        if($altra_url)
        {
            return ['result' => 0,'msg' => 'Attenzione questo seo è già associato ad un\'altra url'];
        }

        try{
            $url = Url::find($url_id);
            $url->seo_id = $seo_id;
            $url->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Url associata con successo!'];
    }

    public function delete_associazione_url(Request $request, $id)
    {
        $url = Url::find($id);
        $url->seo_id = null;
        $url->save();
        return back()->with('success','Associazione rimossa!');
    }

    public function associa_model(Request $request, $id)
    {
        $seo = Seo::find($id);
        $urlable_types = [
            'App\Model\Page',
            'App\Model\Macrocategory',
            'App\Model\Category',
            'App\Model\Product',
            'App\Model\Pairing'
        ];
        $params = [
            'urlable_types' => $urlable_types,
            'seo'=> $seo,
            'form_name' => 'form_associa_model',
        ];
        return view('cms.seo.associa_model',$params);
    }

    public function store_associazione_model(Request $request)
    {
        $seo_id = $request->seo_id;
        $url_type = $request->url_type;
        $seo = Seo::find($seo_id);

        //controllo che questo tipo non sia già stato associato
        $altra_associazione = Seo::where('bind_to',$url_type)->where('locale',$seo->locale)->first();
        if($altra_associazione)
        {
            return ['result' => 0,'msg' => 'Attenzione questo tipo di url è già stata associata'];
        }

        try{

            $seo->bind_to = $url_type;
            $seo->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        return ['result' => 1,'msg' => 'Seo associato con successo!'];
    }

    public function delete_associazione_model(Request $request, $id)
    {
        $seo = Seo::find($id);
        $seo->bind_to = null;
        $seo->save();
        return back()->with('success','Associazione rimossa!');
    }

    public function switch_homepage(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Seo::find($id);
            //ci deve essere solo un seo per homepage/lingua
            if($stato == 1)
            {
                $seos = Seo::where('locale',$item->locale)->get();
                foreach ($seos as $seo)
                {
                    $seo->homepage = 0;
                    $seo->save();
                }
            }

            $item->homepage = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

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
        $seo = Seo::find($id);

        $params = [
            'seo' => $seo,
            'form_name' => 'form_edit_seo',
            'title_page' => 'Modifica SEO'
        ];

        return view('cms.seo.edit',$params);
    }

    public function update(Request $request, $id)
    {
        $seo = Seo::find($id);

        try{

            $seo->locale = $request->lang;
            $seo->title = $request->title;
            $seo->description = $request->description;
            $seo->h1 = $request->h1;
            $seo->h2 = $request->h2;
            $seo->keywords = $request->keywords;
            $seo->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.seo');
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
        $seo = Seo::find($id);

        //cerco la url a cui è associata la seo se c'è
        $url = Url::where('seo_id',$seo->id)->first();
        if($url)
        {
            $url->seo_id = null;
            $url->save();
        }

        $seo->delete();
        return back()->with('success','Elemento cancellato!');
    }
}
