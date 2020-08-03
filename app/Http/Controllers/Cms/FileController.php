<?php

namespace App\Http\Controllers\Cms;

use App\Model\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $params = [];
        return view('cms.file.index',$params);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = File::find($id);

        // ATTENZIONE!!! elimino solo l'associazione dalla tabella file
        // ma non i file...prendo la variabile $filename per implementazione futura
        $filename = $file->path;

        $file->delete();

        return back()->with('success','Elemento cancellato!');
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
            $file = File::find($id);
            $file->order = $ordine;
            $file->save();
        }
        return;
    }
}
