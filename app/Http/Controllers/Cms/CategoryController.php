<?php

namespace App\Http\Controllers\Cms;

use App\Model\Category;
use App\Model\Domain;
use App\Model\File;
use App\Model\Macrocategory;
use App\Model\Pairing;
use App\Model\Product;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorie = Category::orderBy('order', 'desc')->get();
        $params = [
            'title_page' => 'Categorie',
            'categorie' => $categorie,
        ];
        return view('cms.category.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $macros = Macrocategory::all();

        $params = [
            'form_name' => 'form_create_category',
            'macros' => $macros
        ];
        return view('cms.category.create',$params);
    }


    public function store(Request $request)
    {
        $langs = \Config::get('langs');

        try{
            $categoria = new Category();
            $categoria->macrocategory_id = $request->macrocategory_id;
            foreach ($langs as $lang)
            {
                $categoria->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $categoria->{'desc_'.$lang} = $request->{'desc_'.$lang};
            }
            $categoria->save();
            $categoria_id = $categoria->id;

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $macro = Macrocategory::find($categoria->macrocategory_id);

        //1# Creo una url di default per ogni lingua
        foreach ($langs as $lang)
        {
            $domain = Domain::where('locale',$lang)->first();
            try{
                $url = new Url();
                $url->domain_id = $domain->id;
                $url->locale = $lang;
                $url->slug = Str::slug( $categoria->{'nome_'.$lang}, '-');
                $url->urlable_id = $categoria_id;
                $url->urlable_type = 'App\Model\Category';
                $url->save();
            }
            catch(\Exception $e)
            {
                return ['result' => 0,'msg' => $e->getMessage()];
            }
        }
        //1# Fine

        $url = url('/cms/category');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    public function move_up(Request $request,$id)
    {
        $cat = Category::find($id);
        $cat->moveOrderUp();
        return back();
    }

    public function move_down(Request $request,$id)
    {
        $cat = Category::find($id);
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
        $categoria = Category::find($id);
        $params = [
            'categoria' => $categoria,
            'form_name' => 'form_edit_categoria'
        ];

        return view('cms.category.edit',$params);
    }


    public function update(Request $request, $id)
    {
        $categoria = Category::find($id);

        $langs = \Config::get('langs');

        try{

            foreach ($langs as $lang)
            {
                $categoria->{'nome_'.$lang} = $request->{'nome_'.$lang};
                $categoria->{'desc_'.$lang} = $request->{'desc_'.$lang};
            }
            $categoria->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.categorie');
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
        $categoria = Category::find($id);

        $products = Product::where('category_id',$categoria->id)->get();

        if($products->count() > 0)
        {
            return back()->with('error','Non puoi eliminare questa categoria perchè ci sono prodotti associati ad essa!');
        }

        $pairings = Pairing::where('category_id',$categoria->id)->get();
        if($pairings->count() > 0)
        {
            return back()->with('error','Non puoi eliminare questa categoria perchè ci sono abbinamenti associati ad essa!');
        }



        $categoria->delete();

        //elimino anche le url associate alla macro
        $urls = Url::where('urlable_id',$categoria->id)->where('urlable_type','App\Model\Category')->get();
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
            $item = Category::find($id);
            $item->stato = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }
}
