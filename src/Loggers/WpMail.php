<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;

class WpMail implements LoggerContract
{
    use LogHelper;

    /**
     * Register any filters and actions
     * that need to be called in order to log the outgoing mail
     */
    public function __construct()
    {
        add_action('wp_mail', [$this, 'recordMail'], 999999);
        add_action('wp_mail_failed', [$this, 'recordError'], 999999);
    }

    public function recordMail($args)
    {
        $this->saveMail($this->getMailArgs($args));
    }

    public function recordError($error)
    {
        $this->saveError($error->errors['wp_mail_failed'][0]);
    }

    /**
     * Transform the incoming details of the mail into the
     * correct format for our log (data fractal)
     *
     * @param array $args the details of the mail going to be sent
     * @return array must return an array in the same format
     */
    protected function getMailArgs($args)
    {
        return [
            'time' => time(),
            'email_to' => GeneralHelper::arrayToString($args['to']),
            'subject' => $args['subject'],
            'message' => $this->sanitiseInput($args['message']),
            'backtrace_segment' => json_encode($this->getBacktrace()),
            'status' => 1,
            'attachments' => json_encode($this->getAttachmentLocations($args['attachments'])),
            'additional_headers' => json_encode($args['headers'])
        ];
    }
}
