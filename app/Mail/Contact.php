<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Contact extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $config = \Config::get('website_config');
        $sito = $config['sito'];

        $params = [
            'nome' => $this->data['nome'],
            'email'=> $this->data['email'],
            'messaggio' => $this->data['messaggio'],
            'sito' => $sito,
        ];
        return $this->from($config['email'])
            ->subject('Contatto dal sito web '.$sito )
            ->view('website.email.contatti',$params);
    }
}
