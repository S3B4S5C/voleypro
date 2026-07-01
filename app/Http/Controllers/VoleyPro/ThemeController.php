<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ThemeController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('VoleyPro/Theme', [
            'temas' => DB::table('temas')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),

            'preferencia' => DB::table('preferencias_usuario')
                ->where('usuario_id', $request->user()->id)
                ->first(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'tema_id' => ['required', 'exists:temas,id'],
            'tamano_fuente' => ['required', 'in:Pequeño,Normal,Grande'],
            'contraste' => ['required', 'in:Normal,Alto'],
            'modo_automatico' => ['boolean'],
        ], [
            'tema_id.required' => 'Debe seleccionar un tema.',
            'tema_id.exists' => 'El tema seleccionado no existe.',
            'tamano_fuente.required' => 'Debe seleccionar un tamaño de fuente.',
            'tamano_fuente.in' => 'El tamaño de fuente seleccionado no es válido.',
            'contraste.required' => 'Debe seleccionar un contraste.',
            'contraste.in' => 'El contraste seleccionado no es válido.',
        ]);

        DB::table('preferencias_usuario')->updateOrInsert(
            ['usuario_id' => $request->user()->id],
            [
                'tema_id' => $data['tema_id'],
                'tamano_fuente' => $data['tamano_fuente'],
                'contraste' => $data['contraste'],
                'modo_automatico' => (bool) ($data['modo_automatico'] ?? false),
            ]
        );

        return redirect()
            ->route('tema.edit')
            ->with('success', 'Preferencias actualizadas correctamente.');
    }
}
