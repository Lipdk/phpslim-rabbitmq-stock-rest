<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Swift_Mailer;
use Swift_Message;
use App\Models\User;
use App\Models\UserStockRequest;

class StockController
{
    protected Swift_Mailer $mailer;

    protected User $user;

    public function __construct(Swift_Mailer $mailer, User $user)
    {
        $this->mailer = $mailer;
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function quote(Request $request, Response $response, array $args): Response
    {
        // Get User
        $userEmail = $request->getHeader('email')[0];
        $user = $this->user->getUserByEmail($userEmail);

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
        $userStockRequest = new UserStockRequest();
        $userStockRequest->user_id = $user->id;
        $userStockRequest->response = json_encode($stock);
        $userStockRequest->save();

        // Remove some data that isn't supposed to be there according to the Wiki, and format numbers
        unset($stock['volume'], $stock['time'], $stock['date']);
        $stock['name'] = $stockCode;
        $stock['open'] = number_format((float)$stock['open'], 2, '.', '' );
        $stock['high'] = number_format((float)$stock['high'], 2, '.', '' );
        $stock['low'] = number_format((float)$stock['low'], 2, '.', '' );
        $stock['close'] = number_format((float)$stock['close'], 2, '.', '' );
        $jsonStock = json_encode($stock);

        // TODO: Send Email to the user that sent the Request
        // TODO: Move code to a Helper or a Service
        // TODO: Use RabbitMQ to Send the Emails
        if ($jsonStock) {
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

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Exception
     */
    public function history(Request $request, Response $response, array $args): Response
    {
        // Get User
        // TODO: The user should come from authentication, this is just a test
        $user = User::find(1);

        if (empty($user)) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $userStockRequests = UserStockRequest::where('user_id', $user->id)
            ->select(['response', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        $arr = $userStockRequests->toArray();

        if (!empty($arr)) {
            // Format array, decoding the response and handling the data according to the Wiki
            $arr = array_map(function ($item) {
                $a = json_decode($item['response'], true);
                $date = new \DateTime($item['created_at']);
                $a['date'] = $date->format("Y-m-d\TH:i:sp");
                unset($a['time'], $a['volume']);
                return $a;
            }, $arr);
        }

        $response->getBody()->write(json_encode($arr));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
