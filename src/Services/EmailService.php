<?php

namespace App\Services;

use App\Utilities\Config;
use Stdclass;
use Swift_Mailer;

class EmailService
{
    protected Swift_Mailer $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param string $subject
     * @param array $to
     * @param string $htmlMessage
     * @return int
     */
    public function sendMail(string $subject, array $to, string $htmlMessage)
    {
        $msg = $this->mailer->createMessage()
            ->setSender(Config::getMailerDefaultSender())
            ->setSubject($subject)
            ->setTo($to)
            ->setBody($htmlMessage, 'text/html');

        return $this->mailer->send($msg);
    }

    /**
     * @param array $to
     * @param array $stock
     * @return int
     */
    public function sendStockInformationEmail(array $to, array $stock = [])
    {
        $stockName = $stock['name'] ?? $stock['symbol'];
        $subject = "Stock Quote for {$stockName}";
        $date = new \DateTime;
        $html = <<<HTML
<html>
    <body>
        <h1>{$subject}</h1>
        <p>Stock information requested at: {$date->format('Y-m-d H:m')}</p>
        <p>Symbol: {$stock['symbol']}</p>
        <p>Open: {$stock['open']}</p>
        <p>High: {$stock['high']}</p>
        <p>Low: {$stock['low']}</p>
        <p>Close: {$stock['close']}</p>
    </body>
</html>
HTML;

        return $this->sendMail($subject, $to, $html);
    }
}
