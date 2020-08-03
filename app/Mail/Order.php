<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Order extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $config = \Config::get('website_config');
        $sito = $config['sito'];

        $params = [

            'order' => $this->order,
            'sito' => $sito,
        ];
        return $this->from('postmaster@chess-store.it')
            ->subject(trans('msg.conferma_ordine'))
            ->view('website.email.order',$params);
    }
}