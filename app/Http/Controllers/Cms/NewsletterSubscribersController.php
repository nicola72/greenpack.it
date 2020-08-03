<?php
namespace App\Http\Controllers\Cms;

use App\Model\NewsletterSubscriber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class NewsletterSubscribersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $iscritti = NewsletterSubscriber::all();
        $params = [
            'title_page' => 'Iscritti Newsletter',
            'iscritti' => $iscritti
        ];

        return view('cms.subscribers.index',$params);
    }

    public function destroy($id)
    {
        $iscritto = NewsletterSubscriber::find($id);

        $iscritto->delete();
        return back()->with('success','Elemento cancellato!');
    }
}
