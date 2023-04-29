<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Swift_Message;

class StockController
{
    /** @var \Swift_Mailer */
    protected $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function quote(Request $request, Response $response, array $args): Response
    {
        $query = $request->getQueryParams();
        $stockCode = $query['q'] ?? null;
        $curlUrl = $_ENV['STOCK_API_URL'] ?? '';
        $curlUrl = sprintf($curlUrl, $stockCode);

        if (empty($stockCode) || empty($curlUrl)) {
            $response->getBody()->write(json_encode(['error' => 'Invalid request']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        // Make the Curl Request
        // TODO: Move code to a Helper or a Service
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $curlUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $curlResponse = curl_exec($curl);
        curl_close($curl);

        if (empty($curlResponse)) {
            $response->getBody()->write(json_encode([]));
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $rows = array_map('str_getcsv', explode("\n", $curlResponse));
        $stock = array_combine(array_map('strtolower', $rows[0] ?? []), $rows[1] ?? []);

        // TODO: Save the $stock information into a Log table, keep together with the CURL Request

        // Remove some data that isn't supposed to be there according to the Wiki, and format numbers
        unset($stock['volume'], $stock['time'], $stock['date']);
        $stock['name'] = $stockCode;
        $stock['open'] = (float)$stock['open'];
        $stock['high'] = (float)$stock['high'];
        $stock['low'] = (float)$stock['low'];
        $stock['close'] = (float)$stock['close'];
        $jsonStock = json_encode($stock);

        // TODO: Send Email to the user that sent the Request
        // TODO: Move code to a Helper or a Service
        // TODO: Use RabbitMQ to Send the Emails
        if ($jsonStock) {
            $user = new \stdClass;
            $user->name = 'User Name';
            $user->email = 'user_email@mail.com';

            $subject = "Stock Quote for {$stockCode} ({$stock['symbol']})";

            $html = <<<HTML
<html>
    <body>
        <h1>{$subject}</h1>
        <p>Open: {$stock['open']}</p>
        <p>High: {$stock['high']}</p>
        <p>Low: {$stock['low']}</p>
        <p>Close: {$stock['close']}</p>
    </body>
</html>
HTML;
            /** @var Swift_Message $msg */
            $msg = $this->mailer->createMessage()
                ->setSender($_ENV['MAILER_DEFAULT_FROM'] ?? 'admin@email.com')
                ->setSubject($subject)
                ->setTo([$user->email => $user->name])
                ->setBody($html, 'text/html');
            $this->mailer->send($msg);
        }

        $response->getBody()->write($jsonStock);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
