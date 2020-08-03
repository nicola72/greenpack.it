<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class Registration extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $clear_pwd;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,$clear_pwd)
    {
        $this->user = $user;
        $this->clear_pwd = $clear_pwd;
    }

    public function build()
    {
        $config = \Config::get('website_config');
        $sito = $config['sito'];

        $params = [

            'user' => $this->user,
            'clear_pwd' => $this->clear_pwd,
            'sito' => $sito,
        ];
        return $this->from('postmaster@chess-store.it')
            ->subject(trans('msg.conferma_registrazione'))
            ->view('website.email.registration',$params);
    }
}