<?php
namespace App\Http\Controllers\Cms;


use App\Model\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade;


class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();

        $params = [
            'title_page' => 'Ordini',
            'orders' => $orders,
        ];
        return view('cms.order.index',$params);
    }

    public function order(Request $request,$id)
    {
        $order = Order::find($id);

        $params = [
            'title_page' => 'Ordine '.$order->id,
            'order' => $order
        ];

        return view('cms.order.order',$params);
    }

    public function order_print(Request $request, $id)
    {
        $order = Order::find($id);

        $params = [
            'title_page' => 'Ordine '.$order->id,
            'order' => $order
        ];

        return view('cms.order.print',$params);
    }

    public function pdf(Request $request, $id)
    {
        $order = Order::find($id);

        $params = [
            'title_page' => 'Ordine '.$order->id,
            'order' => $order
        ];

        $pdf = \PDF::loadView('cms.order.order_pdf', $params);
        return $pdf->stream('ordine_'.$order->id.'.pdf');

    }
}
