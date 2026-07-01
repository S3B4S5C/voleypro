<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use App\Services\PagoFacil\PagoFacilClient;
use App\Support\VoleyProAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PagoFacilController extends Controller
{
    public function generar(int $pago, PagoFacilClient $client)
    {
        abort_unless(VoleyProAccess::userOwnsPayment(request()->user(), $pago), 403, 'No tiene permiso para operar este pago.');
        try {
            $data = $this->getPagoCompleto($pago);

            $paymentNumber = $data->payment_number;

            if (!$paymentNumber) {
                $paymentNumber = $this->buildPaymentNumber($pago);
            }

            $clientName = trim(($data->usuario_nombre ?? '') . ' ' . ($data->usuario_apellido ?? ''));
            $documentId = $data->ci ?: '0';
            $phone = $this->safePhone($data->telefono);
            $email = $this->safeEmail($data->email);
            $amount = (float) $data->monto;

            $product = 'Inscripción ' . $data->oferta_nombre . ' - Cuota ' . $data->numero_cuota;

            $qr = $client->generateQr(
                clientName: $clientName,
                documentId: $documentId,
                phoneNumber: $phone,
                email: $email,
                paymentNumber: $paymentNumber,
                amount: $amount,
                product: $product
            );

            DB::table('pagos')->where('id', $pago)->update([
                'estado' => 'Pendiente',
                'payment_number' => $paymentNumber,
                'pagofacil_transaction_id' => $qr['transactionId'] ?? null,
                'payment_method_transaction_id' => $qr['paymentMethodTransactionId'] ?? null,
                'pagofacil_status' => 'PENDING',
                'qr_base64' => $qr['qrBase64'] ?? null,
                'checkout_url' => $qr['checkoutUrl'] ?? null,
                'deep_link' => $qr['deepLink'] ?? null,
                'qr_content_url' => $qr['qrContentUrl'] ?? null,
                'universal_url' => $qr['universalUrl'] ?? null,
                'expiration_date' => $qr['expirationDate'] ?? null,
            ]);

            return back()->with('success', 'QR de PagoFácil generado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error generando QR PagoFácil', [
                'pago_id' => $pago,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'pagofacil' => 'Error generando QR PagoFácil: ' . $e->getMessage(),
            ]);
        }
    }

    public function verificar(int $pago, PagoFacilClient $client)
    {
        abort_unless(VoleyProAccess::userOwnsPayment(request()->user(), $pago), 403, 'No tiene permiso para operar este pago.');
        try {
            $local = DB::table('pagos')->where('id', $pago)->first();

            abort_if(!$local, 404, 'Pago no encontrado.');

            if (!$local->pagofacil_transaction_id) {
                return back()->withErrors([
                    'pagofacil' => 'El pago no tiene transacción PagoFácil asociada.',
                ]);
            }

            $query = $client->queryTransaction($local->pagofacil_transaction_id);

            $estadoPagoFacil = (string) ($query['paymentStatus'] ?? '');
            $estadoLocal = $this->mapEstado($estadoPagoFacil);

            DB::table('pagos')->where('id', $pago)->update([
                'estado' => $estadoLocal,
                'pagofacil_status' => $estadoPagoFacil,
                'pagofacil_payment_date' => $query['paymentDate'] ?? null,
                'pagofacil_payment_time' => $query['paymentTime'] ?? null,
                'fecha_verificacion' => now(),
            ]);

            if ($estadoLocal === 'Pagado') {
                DB::table('cuotas_pago')->where('id', $local->cuota_pago_id)->update([
                    'estado' => 'Pagada',
                ]);

                $this->actualizarEstadoPlanPagoPorCuota($local->cuota_pago_id);
            }

            return back()->with('success', 'Pago verificado correctamente. Estado local: ' . $estadoLocal);
        } catch (Throwable $e) {
            Log::error('Error verificando PagoFácil', [
                'pago_id' => $pago,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'pagofacil' => 'Error verificando PagoFácil: ' . $e->getMessage(),
            ]);
        }
    }

    public function callback(Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            return $this->success('Callback PagoFácil activo.');
        }

        try {
            $payload = $request->all();

            Log::info('Callback PagoFácil recibido', [
                'payload' => $payload,
            ]);

            $paymentNumber = $this->firstString($payload, [
                'PedidoID',
                'pedidoId',
                'paymentNumber',
                'companyTransactionId',
            ]);

            $fecha = $this->firstString($payload, [
                'Fecha',
                'fecha',
                'paymentDate',
            ]);

            $hora = $this->firstString($payload, [
                'Hora',
                'hora',
                'paymentTime',
            ]);

            $metodoPago = $this->firstString($payload, [
                'MetodoPago',
                'metodoPago',
                'paymentMethod',
                'paymentMethodId',
            ]);

            $estadoPagoFacil = $this->firstString($payload, [
                'Estado',
                'estado',
                'paymentStatus',
            ]);

            if ($paymentNumber === '') {
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'PedidoID es obligatorio.',
                    'values' => false,
                ], 400);
            }

            $estadoLocal = $this->mapEstado($estadoPagoFacil);

            $pagoLocal = DB::table('pagos')
                ->where('payment_number', $paymentNumber)
                ->first();

            if (!$pagoLocal) {
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'No existe pago local para el PedidoID recibido.',
                    'values' => false,
                ], 404);
            }

            DB::table('pagos')
                ->where('id', $pagoLocal->id)
                ->update([
                    'estado' => $estadoLocal,
                    'pagofacil_status' => trim($estadoPagoFacil . ' | ' . $metodoPago),
                    'pagofacil_payment_date' => $fecha,
                    'pagofacil_payment_time' => $hora,
                    'fecha_verificacion' => now(),
                ]);

            if ($estadoLocal === 'Pagado') {
                DB::table('cuotas_pago')->where('id', $pagoLocal->cuota_pago_id)->update([
                    'estado' => 'Pagada',
                ]);

                $this->actualizarEstadoPlanPagoPorCuota($pagoLocal->cuota_pago_id);
            }

            return $this->success('Callback procesado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error procesando callback PagoFácil', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error interno procesando callback.',
                'values' => false,
            ], 500);
        }
    }

    public function metodos(PagoFacilClient $client): JsonResponse
    {
        try {
            return response()->json([
                'ok' => true,
                'metodos' => $client->listEnabledServices(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmarDemo(int $pago)
    {
        abort_unless(VoleyProAccess::userOwnsPayment(request()->user(), $pago), 403, 'No tiene permiso para operar este pago.');
        $local = DB::table('pagos')->where('id', $pago)->first();

        abort_if(!$local, 404, 'Pago no encontrado.');

        DB::table('pagos')->where('id', $pago)->update([
            'estado' => 'Pagado',
            'pagofacil_status' => 'DEMO_PAID',
            'pagofacil_payment_date' => now()->format('Y-m-d'),
            'pagofacil_payment_time' => now()->format('H:i:s'),
            'fecha_verificacion' => now(),
        ]);

        DB::table('cuotas_pago')->where('id', $local->cuota_pago_id)->update([
            'estado' => 'Pagada',
        ]);

        $this->actualizarEstadoPlanPagoPorCuota($local->cuota_pago_id);

        return back()->with('success', 'Pago confirmado en modo demo.');
    }

    private function getPagoCompleto(int $pago)
    {
        abort_unless(VoleyProAccess::userOwnsPayment(request()->user(), $pago), 403, 'No tiene permiso para operar este pago.');
        $data = DB::table('pagos')
            ->join('cuotas_pago', 'pagos.cuota_pago_id', '=', 'cuotas_pago.id')
            ->join('planes_pago', 'cuotas_pago.plan_pago_id', '=', 'planes_pago.id')
            ->join('inscripciones', 'planes_pago.inscripcion_id', '=', 'inscripciones.id')
            ->join('ofertas_academicas', 'inscripciones.oferta_id', '=', 'ofertas_academicas.id')
            ->join('usuarios', 'pagos.usuario_id', '=', 'usuarios.id')
            ->where('pagos.id', $pago)
            ->select(
                'pagos.*',
                'cuotas_pago.numero_cuota',
                'planes_pago.id as plan_pago_id',
                'ofertas_academicas.nombre as oferta_nombre',
                'usuarios.nombre as usuario_nombre',
                'usuarios.apellido as usuario_apellido',
                'usuarios.ci',
                'usuarios.telefono',
                'usuarios.email'
            )
            ->first();

        abort_if(!$data, 404, 'Pago no encontrado.');

        return $data;
    }

    private function actualizarEstadoPlanPagoPorCuota(int $cuotaPagoId): void
    {
        $cuota = DB::table('cuotas_pago')->where('id', $cuotaPagoId)->first();

        if (!$cuota) {
            return;
        }

        $pendientes = DB::table('cuotas_pago')
            ->where('plan_pago_id', $cuota->plan_pago_id)
            ->whereIn('estado', ['Pendiente', 'Vencida'])
            ->count();

        if ($pendientes === 0) {
            DB::table('planes_pago')->where('id', $cuota->plan_pago_id)->update([
                'estado' => 'Pagado',
            ]);
        } else {
            DB::table('planes_pago')->where('id', $cuota->plan_pago_id)->update([
                'estado' => 'Activo',
            ]);
        }
    }

    private function buildPaymentNumber(int $pagoId): string
    {
        return 'VP-' . $pagoId . '-' . now()->format('YmdHis');
    }

    private function mapEstado(?string $estadoPagoFacil): string
    {
        if ($estadoPagoFacil === null || trim($estadoPagoFacil) === '') {
            return 'Pendiente';
        }

        $estado = mb_strtolower(trim($estadoPagoFacil));

        if (
            $estado === (string) config('pagofacil.status.paid')
            || str_contains($estado, 'pagado')
            || str_contains($estado, 'aprobado')
            || str_contains($estado, 'paid')
        ) {
            return 'Pagado';
        }

        if (
            $estado === (string) config('pagofacil.status.review')
            || str_contains($estado, 'revision')
            || str_contains($estado, 'revisión')
            || str_contains($estado, 'review')
        ) {
            return 'Revision';
        }

        if (
            str_contains($estado, 'expirado')
            || str_contains($estado, 'vencido')
            || str_contains($estado, 'expired')
        ) {
            return 'Expirado';
        }

        if (
            str_contains($estado, 'cancelado')
            || str_contains($estado, 'anulado')
            || str_contains($estado, 'cancelled')
        ) {
            return 'Cancelado';
        }

        if (
            str_contains($estado, 'rechazado')
            || str_contains($estado, 'rejected')
        ) {
            return 'Rechazado';
        }

        return 'Pendiente';
    }

    private function firstString(array $payload, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && $payload[$key] !== null) {
                return trim((string) $payload[$key]);
            }
        }

        return '';
    }

    private function safePhone(?string $phone): string
    {
        $phone = trim((string) $phone);

        return $phone !== '' ? $phone : '70000000';
    }

    private function safeEmail(?string $email): string
    {
        $email = trim((string) $email);

        return $email !== '' ? $email : 'no-reply@voleypro.test';
    }

    private function success(string $message): JsonResponse
    {
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => $message,
            'values' => true,
        ]);
    }
}
