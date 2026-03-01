<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controller: ParametrosGralController
 *
 * Mantenimiento de parámetros generales por módulo (Company DB).
 *
 * @see docs/04-tareas/000-Generalidades/TR-007-Parametros-generales.md
 */
class ParametrosGralController extends Controller
{
    private const TIPOS_VALIDOS = ['S', 'T', 'I', 'D', 'B', 'N'];

    /**
     * GET /api/v1/parametros-gral?programa={programa}
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $programa = $request->query('programa');
        if (!$programa) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'El parámetro programa es obligatorio',
                'resultado' => (object) [],
            ], 422);
        }

        $rows = DB::connection('company')
            ->table('PQ_PARAMETROS_GRAL')
            ->where('Programa', $programa)
            ->orderBy('Clave')
            ->get();

        $items = $rows->map(fn ($r) => [
            'programa' => $r->Programa,
            'clave' => $r->Clave,
            'tipoValor' => $r->tipo_valor,
            'valorString' => $r->Valor_String,
            'valorText' => $r->Valor_Text,
            'valorInt' => $r->Valor_Int,
            'valorDateTime' => $r->Valor_DateTime ? date('c', strtotime($r->Valor_DateTime)) : null,
            'valorBool' => $r->Valor_Bool,
            'valorDecimal' => $r->Valor_Decimal !== null ? (float) $r->Valor_Decimal : null,
        ]);

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => ['items' => $items],
        ]);
    }

    /**
     * PUT /api/v1/parametros-gral/{programa}/{clave}
     */
    public function update(Request $request, string $programa, string $clave): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $exists = DB::connection('company')
            ->table('PQ_PARAMETROS_GRAL')
            ->where('Programa', $programa)
            ->where('Clave', $clave)
            ->first();

        if (!$exists) {
            return response()->json([
                'error' => 404,
                'respuesta' => 'Parámetro no encontrado',
                'resultado' => (object) [],
            ], 404);
        }

        $tipoValor = $exists->tipo_valor ?? 'S';
        $validator = $this->buildValidator($request->all(), $tipoValor);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        $columna = $this->getColumnaPorTipo($tipoValor);
        $valor = $data['valor'] ?? null;

        $update = [$columna => $this->castValor($valor, $tipoValor)];

        DB::connection('company')
            ->table('PQ_PARAMETROS_GRAL')
            ->where('Programa', $programa)
            ->where('Clave', $clave)
            ->update($update);

        $row = DB::connection('company')
            ->table('PQ_PARAMETROS_GRAL')
            ->where('Programa', $programa)
            ->where('Clave', $clave)
            ->first();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Parámetro actualizado',
            'resultado' => [
                'programa' => $row->Programa,
                'clave' => $row->Clave,
                'tipoValor' => $row->tipo_valor,
                'valorString' => $row->Valor_String,
                'valorText' => $row->Valor_Text,
                'valorInt' => $row->Valor_Int,
                'valorDateTime' => $row->Valor_DateTime ? date('c', strtotime($row->Valor_DateTime)) : null,
                'valorBool' => (bool) $row->Valor_Bool,
                'valorDecimal' => $row->Valor_Decimal !== null ? (float) $row->Valor_Decimal : null,
            ],
        ]);
    }

    private function buildValidator(array $data, string $tipoValor): \Illuminate\Validation\Validator
    {
        $rules = ['valor' => ['nullable']];
        switch ($tipoValor) {
            case 'S':
                $rules['valor'] = ['nullable', 'string', 'max:255'];
                break;
            case 'T':
                $rules['valor'] = ['nullable', 'string'];
                break;
            case 'I':
                $rules['valor'] = ['nullable', 'integer'];
                break;
            case 'D':
                $rules['valor'] = ['nullable', 'date'];
                break;
            case 'B':
                $rules['valor'] = ['nullable', 'boolean'];
                break;
            case 'N':
                $rules['valor'] = ['nullable', 'numeric'];
                break;
        }

        return Validator::make($data, $rules);
    }

    private function getColumnaPorTipo(string $tipo): string
    {
        return match ($tipo) {
            'S' => 'Valor_String',
            'T' => 'Valor_Text',
            'I' => 'Valor_Int',
            'D' => 'Valor_DateTime',
            'B' => 'Valor_Bool',
            'N' => 'Valor_Decimal',
            default => 'Valor_String',
        };
    }

    private function castValor(mixed $valor, string $tipo): mixed
    {
        if ($valor === null) {
            return null;
        }
        return match ($tipo) {
            'I' => (int) $valor,
            'B' => (bool) $valor,
            'N' => (float) $valor,
            'D' => is_string($valor) ? $valor : date('Y-m-d H:i:s', strtotime((string) $valor)),
            default => (string) $valor,
        };
    }
}
