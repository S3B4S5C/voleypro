<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LogController extends Controller
{
    public function index()
    {
        return Inertia::render('VoleyPro/Logs', [
            'loginLogs' => DB::table('bitacora_accesos')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),

            'accessLogs' => DB::table('bitacora_recursos')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),

            'searchLogs' => DB::table('busquedas')
                ->orderByDesc('id')
                ->limit(50)
                ->get(),
        ]);
    }
}
