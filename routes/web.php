<?php

use App\Http\Controllers\VoleyPro\CrudController;
use App\Http\Controllers\VoleyPro\DashboardController;
use App\Http\Controllers\VoleyPro\LogController;
use App\Http\Controllers\VoleyPro\PagoFacilController;
use App\Http\Controllers\VoleyPro\ReportController;
use App\Http\Controllers\VoleyPro\SearchController;
use App\Http\Controllers\VoleyPro\SecurityController;
use App\Http\Controllers\VoleyPro\ThemeController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::match(['GET', 'POST'], '/pagofacil/callback', [PagoFacilController::class, 'callback'])
    ->name('pagofacil.callback')
    ->withoutMiddleware([ValidateCsrfToken::class]);

if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}

if (file_exists(__DIR__ . '/settings.php')) {
    require __DIR__ . '/settings.php';
}

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/buscar', [SearchController::class, 'index'])
        ->name('buscar.index');

    Route::get('/reportes', [ReportController::class, 'index'])
        ->middleware('vp.can:reportes,puede_ver')
        ->name('reportes.index');

    Route::get('/reportes/exportar', [ReportController::class, 'exportar'])
        ->middleware('vp.can:reportes,puede_ver')
        ->name('reportes.exportar');

    Route::get('/bitacora', [LogController::class, 'index'])
        ->middleware('vp.can:bitacora,puede_ver')
        ->name('bitacora.index');

    Route::get('/seguridad', [SecurityController::class, 'index'])
        ->middleware('vp.can:seguridad,puede_ver')
        ->name('seguridad.index');

    Route::get('/tema', [ThemeController::class, 'edit'])
        ->middleware('vp.can:temas,puede_ver')
        ->name('tema.edit');

    Route::post('/tema', [ThemeController::class, 'update'])
        ->middleware('vp.can:temas,puede_editar')
        ->name('tema.update');

    Route::get('/pagofacil/metodos', [PagoFacilController::class, 'metodos'])
        ->middleware('vp.can:pagos,puede_ver')
        ->name('pagofacil.metodos');

    Route::post('/pagos/{pago}/pago-facil/generar', [PagoFacilController::class, 'generar'])
        ->middleware('vp.can:pagos,puede_ver')
        ->name('pago-facil.generar');

    Route::post('/pagos/{pago}/pago-facil/verificar', [PagoFacilController::class, 'verificar'])
        ->middleware('vp.can:pagos,puede_ver')
        ->name('pago-facil.verificar');

    Route::post('/pagos/{pago}/pago-facil/confirmar-demo', [PagoFacilController::class, 'confirmarDemo'])
        ->middleware('vp.can:pagos,puede_editar')
        ->name('pago-facil.confirmar-demo');

    Route::get('/{resource}', [CrudController::class, 'index'])->name('crud.index');
    Route::get('/{resource}/crear', [CrudController::class, 'create'])->name('crud.create');
    Route::post('/{resource}', [CrudController::class, 'store'])->name('crud.store');
    Route::get('/{resource}/{id}/editar', [CrudController::class, 'edit'])->name('crud.edit');
    Route::put('/{resource}/{id}', [CrudController::class, 'update'])->name('crud.update');
    Route::delete('/{resource}/{id}', [CrudController::class, 'destroy'])->name('crud.destroy');
});
