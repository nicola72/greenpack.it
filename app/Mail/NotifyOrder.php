<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyOrder extends Mailable
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
        $params = [
            'order' => $this->order,
        ];
        return $this->from('postmaster@chess-store.it')
            ->subject('Nuovo Ordine da sito chess-store.it')
            ->view('website.email.notify_order',$params);
    }
}