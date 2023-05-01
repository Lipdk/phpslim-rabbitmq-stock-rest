<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Queue\PublishToQueueService;
use App\Services\StockService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Swift_Mailer;
use App\Models\User;
use App\Models\UserStockRequest;

class StockController
{
    protected Swift_Mailer $mailer;
    protected User $user;
    protected PublishToQueueService $publishToQueueService;
    protected StockService $stockService;

    public function __construct(
        Swift_Mailer $mailer,
        User $user,
        PublishToQueueService $publishToQueueService,
        StockService $stockService
    ) {
        $this->mailer = $mailer;
        $this->user = $user;
        $this->publishToQueueService = $publishToQueueService;
        $this->stockService = $stockService;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function quote(Request $request, Response $response, array $args): Response
    {
        $userEmail = $request->getHeader('email')[0];
        $user = $this->user->getUserByEmail($userEmail);

        $query = $request->getQueryParams();
        $stockCode = $query['q'] ?? null;

        if (empty($stockCode)) {
            $response->getBody()->write(json_encode(['error' => 'Invalid request']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(422);
        }

        $stock = $this->stockService->getStockInfo($stockCode);

        if (empty($stock) || !isset($stock['symbol'])) {
            $response->getBody()->write(json_encode(['error' => 'Stock not found!']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        // Store the request in the Log table
        $userStockRequest = new UserStockRequest();
        $userStockRequest->user_id = $user->id;
        $userStockRequest->response = json_encode($stock);
        $userStockRequest->save();

        // Publish to RabbitMQ only the ID of the Log Request
        $this->publishToQueueService->publish($userStockRequest->id);

        // Remove some data that isn't supposed to be there according to the Wiki, and format numbers if needed
        $normalizedStock = $this->stockService->normalizeStockResponse($stock);
        $jsonStock = json_encode($normalizedStock);
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
        $userEmail = $request->getHeader('email')[0];
        $user = $this->user->getUserByEmail($userEmail);

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
