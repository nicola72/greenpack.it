<?php

namespace App\Http\Controllers\Cms;

use App\Model\Domain;
use App\Model\Module;
use App\Model\ModuleConfig;
use App\Model\Page;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modulo = Module::where('nome','website')->first();
        $configs = $modulo->configs;

        $params = ['title_page' => 'Website','configs'=>$configs];
        return view('cms.website.index',$params);
    }

    public function urls(Request $request)
    {
        $type = $request->type;
        if($type != '')
        {

            $urls = Url::where('urlable_type',$type)->get();
        }
        else
        {
            $urls = Url::all();
        }
        $params = ['title_page' => 'Website Urls','urls'=> $urls];
        return view('cms.website.urls',$params);
    }

    public function edit_url(Request $request, $id)
    {
        $url = Url::find($id);
        $domains = Domain::all();
        $params = [
            'form_name' => 'form_edit_url',
            'url' => $url,
            'domains' => $domains
        ];

        return view('cms.website.edit_url',$params);
    }

    public function update_url(Request $request, $id)
    {
        try{
            $url = Url::find($id);
            $url->domain_id = $request->domain_id;
            $url->locale = $request->lang;
            $url->slug = $request->slug;
            $url->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/website/urls');
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => $url];
    }

    public function pages()
    {
        $pages = Page::all();
        $params = ['title_page' => 'Website Pagine','pages'=>$pages];
        return view('cms.website.pages',$params);
    }

    public function create_page()
    {
        $params = [
            'form_name' => 'form_create_page',
        ];
        return view('cms.website.create_page',$params);
    }

    public function store_page(Request $request)
    {
        $langs = \Config::get('langs');
        try{
            $page = new Page();
            $page->nome = $request->nome;
            foreach ($langs as $lang)
            {
                $page->{'label_'.$lang} = $request->{'label_'.$lang};
            }
            $page->save();
            $page_id = $page->id;
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        //1# Creo una url di default per ogni lingua
        $langs = \Config::get('langs');

        foreach ($langs as $lang)
        {
            $domain = Domain::where('locale',$lang)->first();
            try{
                $url = new Url();
                $url->domain_id = $domain->id;
                $url->locale = $lang;
                $url->slug = str_replace(" ","_", $request->nome);
                $url->urlable_id = $page_id;
                $url->urlable_type = 'App\Model\Page';
                $url->save();
            }
            catch(\Exception $e)
            {
                return ['result' => 0,'msg' => $e->getMessage()];
            }
        }
        //1# Fine

        $url = url('/cms/website/pages');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    public function domains()
    {
        $domains = Domain::all();
        $params = ['title_page' => 'Website Domini','domains'=>$domains];
        return view('cms.website.domains',$params);
    }

    public function create_domain()
    {
        $params = [
            'form_name' => 'form_create_domain',
        ];
        return view('cms.website.create_domain',$params);
    }

    public function edit_domain(Request $request,$id)
    {
        $domain = Domain::find($id);

        $params = [
            'form_name' => 'form_edit_domain',
            'domain'     => $domain,
        ];

        return view('cms.website.edit_domain',$params);
    }

    public function update_domain(Request $request,$id)
    {
        try{
            $domain = Domain::find($id);
            $domain->nome = $request->nome;
            $domain->locale = $request->lang;
            $domain->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/website/domains');
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => $url];
    }

    public function store_domain(Request $request)
    {
        try{
            $domain = new Domain();
            $domain->nome = $request->nome;
            $domain->locale = $request->lang;
            $domain->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/website/domains');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_domain(Request $request, $id)
    {
        $domain = Domain::find($id);
        $domain->delete();
        return back()->with('success','Elemento cancellato!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_page(Request $request, $id)
    {
        $page = Page::find($id);
        $page->delete();

        //elimino anche le url associate alla macro
        $urls = Url::where('urlable_id',$page->id)->where('urlable_type','App\Model\Page')->get();
        foreach ($urls as $url)
        {
            $url->delete();
        }
        return back()->with('success','Elemento cancellato!');
    }

    public function page_move_up(Request $request,$id)
    {
        $page = Page::find($id);
        $page->moveOrderUp();
        return back();
    }

    public function page_move_down(Request $request,$id)
    {
        $page = Page::find($id);
        $page->moveOrderDown();
        return back();
    }

    public function switch_menu_page(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Page::find($id);
            $item->menu = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }
}
