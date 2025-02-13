<?php

namespace App\Jobs;

use App\Mail\SendEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\GeneralOptionsModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $subject;
    protected $template;
    protected $parameters;

    public function __construct($email, $subject, $parameters, $template)
    {
        $this->setConfigEmailServer();
        $this->email = $email;
        $this->subject = $subject;
        $this->template = $template;
        $this->parameters = $parameters;
    }

    public function handle()
    {
        try {
            Mail::to($this->email)->send(new SendEmail($this->subject, $this->parameters, $this->template));
        }
        catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }

    private function setConfigEmailServer() {
        $parametersEmailService = GeneralOptionsModel::whereIn('option_name', [
            'smtp_server',
            'smtp_port',
            'smtp_user',
            'smtp_password',
            'smtp_name_from',
            'smtp_encryption',
            'smtp_address_from'
        ])->get()->mapWithKeys(function ($item) {
            return [$item['option_name'] => $item['option_value']];
        })->toArray();

        Config::set('mail.mailers.smtp.host', $parametersEmailService['smtp_server'] ?? null);
        Config::set('mail.mailers.smtp.port', $parametersEmailService['smtp_port'] ?? null);
        Config::set('mail.mailers.smtp.username', $parametersEmailService['smtp_user'] ?? null);
        Config::set('mail.mailers.smtp.password', $parametersEmailService['smtp_password'] ?? null);
        Config::set('mail.from.name', $parametersEmailService['smtp_name_from'] ?? env('MAIL_FROM_NAME'));
        Config::set('mail.mailers.smtp.encryption', $parametersEmailService['smtp_encryption'] ?? null);
        Config::set('mail.from.address', $parametersEmailService['smtp_address_from'] ?? null);
    }
}
