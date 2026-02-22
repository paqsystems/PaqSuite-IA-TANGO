<?php

declare(strict_types=1);

namespace App;

/**
 * Especificación OpenAPI base para la API del MVP (Partes de atención).
 * Todas las respuestas usan el envelope estándar: error, respuesta, resultado.
 * Ver .cursor/rules/06-api-contract.md y docs/api/openapi.md.
 *
 * @OA\OpenApi(
 *     openapi="3.0.3",
 *     info={
 *         @OA\Info(
 *             title="API Partes de Atención - MVP",
 *             version="1.0.0",
 *             description="API REST para el sistema de partes de atención (consultorías y empresas de servicios). Autenticación con Laravel Sanctum (Bearer token)."
 *         )
 *     },
 *     servers={
 *         @OA\Server(url="http://localhost:8000", description="Entorno local"),
 *         @OA\Server(url="/", description="Servidor actual (relativo)")
 *     },
 *     components=@OA\Components(
 *         schemas={
 *             @OA\Schema(
 *                 schema="ApiEnvelope",
 *                 description="Envelope estándar de todas las respuestas",
 *                 required={"error","respuesta","resultado"},
 *                 @OA\Property(property="error", type="integer", description="0=OK, distinto de 0=error"),
 *                 @OA\Property(property="respuesta", type="string", description="Mensaje legible"),
 *                 @OA\Property(property="resultado", type="object", description="Datos o {}")
 *             ),
 *             @OA\Schema(
 *                 schema="ApiErrorResponse",
 *                 allOf={@OA\Schema(ref="#/components/schemas/ApiEnvelope")},
 *                 description="Respuesta de error (error != 0)"
 *             )
 *         },
 *         securitySchemes={
 *             @OA\SecurityScheme(
 *                 securityScheme="bearerAuth",
 *                 type="http",
 *                 scheme="bearer",
 *                 bearerFormat="Sanctum",
 *                 description="Token obtenido vía POST /api/v1/auth/login"
 *             )
 *         }
 *     ),
 *     paths={
 *         @OA\PathItem(
 *             path="/api/v1/auth/login",
 *             post=@OA\Post(
 *                 operationId="authLogin",
 *                 tags={"Auth"},
 *                 summary="Login",
 *                 responses={@OA\Response(response=200, description="OK")}
 *             )
 *         )
 *     },
 *     tags={
 *         @OA\Tag(name="Auth", description="Login, logout, recuperación de contraseña"),
 *         @OA\Tag(name="User", description="Perfil de usuario"),
 *         @OA\Tag(name="Dashboard", description="Resumen dashboard"),
 *         @OA\Tag(name="Reports", description="Informes y consultas"),
 *         @OA\Tag(name="Clientes", description="CRUD clientes y tipos de cliente"),
 *         @OA\Tag(name="Empleados", description="CRUD empleados"),
 *         @OA\Tag(name="Tipos de tarea", description="CRUD tipos de tarea"),
 *         @OA\Tag(name="Tasks", description="Tareas diarias y proceso masivo")
 *     }
 * )
 */
class OpenApi
{
}
