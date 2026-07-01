<?php

namespace App\Services\PagoFacil;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PagoFacilClient
{
    private ?string $accessToken = null;

    public function listEnabledServices(): array
    {
        $token = $this->getAccessToken();

        $response = $this->postJson('/list-enabled-services', $token);

        $this->ensureSuccessful($response, 'listar métodos habilitados');

        return $response['values'] ?? [];
    }

    public function generateQr(
        string $clientName,
        string $documentId,
        string $phoneNumber,
        string $email,
        string $paymentNumber,
        float $amount,
        string $product
    ): array {
        $token = $this->getAccessToken();

        $paymentMethod = $this->resolvePaymentMethod();

        $body = [
            'paymentMethod' => $paymentMethod,
            'clientName' => $clientName,
            'documentType' => 1,
            'documentId' => $documentId,
            'phoneNumber' => $phoneNumber,
            'email' => $email,
            'paymentNumber' => $paymentNumber,
            'amount' => $amount,
            'currency' => config('pagofacil.currency'),
            'clientCode' => config('pagofacil.commerce_id'),
            'callbackUrl' => config('pagofacil.callback_url'),

            'orderDetail' => [
                [
                    'serial' => 1,
                    'product' => $product,
                    'quantity' => 1,
                    'price' => $amount,
                    'discount' => 0,
                    'total' => $amount,
                ],
            ],
        ];

        Log::info('Generando QR PagoFácil', [
            'paymentNumber' => $paymentNumber,
            'amount' => $amount,
            'paymentMethod' => $paymentMethod,
        ]);

        $response = $this->postJson('/generate-qr', $token, $body);

        $this->ensureSuccessful($response, 'generar QR');

        return $response['values'] ?? [];
    }

    public function queryTransaction(int|string $pagofacilTransactionId): array
    {
        $token = $this->getAccessToken();

        $body = [
            'pagofacilTransactionId' => (int) $pagofacilTransactionId,
        ];

        $response = $this->postJson('/query-transaction', $token, $body);

        $this->ensureSuccessful($response, 'consultar transacción');

        return $response['values'] ?? [];
    }

    private function resolvePaymentMethod(): int
    {
        $configured = config('pagofacil.payment_method');

        if ($configured !== null && trim((string) $configured) !== '') {
            return (int) $configured;
        }

        $services = $this->listEnabledServices();

        if (empty($services)) {
            throw new RuntimeException('PagoFácil no devolvió métodos de pago habilitados.');
        }

        $selected = $services[0];

        $paymentMethodId = (int) ($selected['paymentMethodId'] ?? 0);

        if ($paymentMethodId <= 0) {
            throw new RuntimeException('PagoFácil devolvió un método de pago inválido.');
        }

        Log::info('Usando método PagoFácil automático', [
            'paymentMethodId' => $paymentMethodId,
            'paymentMethodName' => $selected['paymentMethodName'] ?? null,
        ]);

        return $paymentMethodId;
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken !== null && trim($this->accessToken) !== '') {
            return $this->accessToken;
        }

        $response = $this->login();

        $this->ensureSuccessful($response, 'login PagoFácil');

        $token = $response['values']['accessToken'] ?? null;

        if (!$token) {
            throw new RuntimeException('PagoFácil no devolvió accessToken.');
        }

        $this->accessToken = $token;

        return $this->accessToken;
    }

    private function login(): array
    {
        $url = rtrim(config('pagofacil.base_url'), '/') . '/login';

        $response = Http::timeout(30)
            ->withOptions([
                'verify' => config('pagofacil.verify_ssl'),
            ])
            ->withHeaders([
                'tcTokenService' => config('pagofacil.token_service'),
                'tcTokenSecret' => config('pagofacil.token_secret'),
                'Response-Language' => 'es',
            ])
            ->post($url);

        Log::info('Respuesta login PagoFácil', [
            'status' => $response->status(),
        ]);

        return $this->decodeResponse($response->body(), 'login PagoFácil');
    }

    private function postJson(string $endpoint, string $token, ?array $body = null): array
    {
        $url = rtrim(config('pagofacil.base_url'), '/') . $endpoint;

        $request = Http::timeout(30)
            ->withOptions([
                'verify' => config('pagofacil.verify_ssl'),
            ])
            ->withToken($token)
            ->withHeaders([
                'Response-Language' => 'es',
                'Content-Type' => 'application/json',
            ]);

        $response = $body === null
            ? $request->post($url)
            : $request->post($url, $body);

        Log::info('Respuesta PagoFácil', [
            'endpoint' => $endpoint,
            'status' => $response->status(),
        ]);

        return $this->decodeResponse($response->body(), $endpoint);
    }

    private function decodeResponse(string $body, string $operation): array
    {
        $json = json_decode($body, true);

        if (!is_array($json)) {
            throw new RuntimeException("PagoFácil devolvió una respuesta no JSON en {$operation}: {$body}");
        }

        return $json;
    }

    private function ensureSuccessful(array $response, string $operation): void
    {
        $error = (int) ($response['error'] ?? 1);
        $message = (string) ($response['message'] ?? 'Error desconocido');

        if ($error !== 0) {
            throw new RuntimeException("Error PagoFácil al {$operation}: {$message}");
        }
    }
}
