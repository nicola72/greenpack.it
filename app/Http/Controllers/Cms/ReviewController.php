<?php
namespace App\Http\Controllers\Cms;

use App\Model\Review;
use App\Model\Url;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::all();

        $params = [
            'reviews' => $reviews,
            'title_page' => 'Recensioni',
        ];
        return view('cms.review.index',$params);
    }

    public function create()
    {
        $params = [
            'form_name' => 'form_create_review',
        ];
        return view('cms.review.create',$params);
    }

    public function store(Request $request)
    {
        try{
            $review = new Review();
            $review->nome = $request->nome;
            $review->messaggio = $request->messaggio;
            $review->data_evento = $request->data_evento;
            $review->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/review');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $review = Review::find($id);
        $params = [
            'review' => $review,
            'form_name' => 'form_edit_review'
        ];

        return view('cms.review.edit',$params);
    }


    public function update(Request $request, $id)
    {
        $review = Review::find($id);


        try{

            $review->nome = $request->nome;
            $review->messaggio = $request->messaggio;
            $review->data_evento = $request->data_evento;
            $review->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.recensioni');
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
        $review = Review::find($id);
        $review->delete();

        return back()->with('success','Elemento cancellato!');
    }

    public function switch_visibility(Request $request)
    {
        $id = $request->id;
        $stato = $request->stato;

        try{
            $item = Review::find($id);
            $item->visibile = $stato;
            $item->save();
        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!'];

    }
}