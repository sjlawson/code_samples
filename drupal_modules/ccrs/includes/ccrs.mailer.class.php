<?php

/**
 * A simple and typical mailer class using php mail()
 */
class ccrsMailer {

    protected $recipients = array();
    protected $sender = 'Moorehead Communications Inc <noreply@mooreheadcomm.com>';
    protected $subject = '';
    protected $message = '';

    public function __construct() { }

    public function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;
    }

    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;
    }

    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    private function validateMessage()
    {
        if (!count($this->recipients)) {
            return false;
        }

        if (empty($this->sender) || empty($this->subject) || empty($this->message)){
            return false;
        }

        return true;
    }

    public function sendCcrsMail()
    {
        if (!$this->validateMessage()) {
            return false;
        }

        if (count($this->recipients) > 1) {
            $strRecipients = implode(', ',$this->recipients);
        } else {
            $strRecipients = $this->recipients[0];
        }

        $headers = "From: " . $this->sender . "\r\n";

        return mail($strRecipients, $this->subject, $this->message, $headers);
    }

}
