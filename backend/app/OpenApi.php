<?php

declare(strict_types=1);

namespace App;

/**
 * Especificación OpenAPI base para la API.
 *
 * @OA\OpenApi(
 *     openapi="3.0.3",
 *     info={
 *         @OA\Info(
 *             title="API PaqSuite-IA-TANGO",
 *             version="1.0.0",
 *             description="API REST. Autenticación con Laravel Sanctum (Bearer token)."
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
 *                 required={"error","respuesta","resultado"},
 *                 @OA\Property(property="error", type="integer"),
 *                 @OA\Property(property="respuesta", type="string"),
 *                 @OA\Property(property="resultado", type="object")
 *             )
 *         },
 *         securitySchemes={
 *             @OA\SecurityScheme(
 *                 securityScheme="bearerAuth",
 *                 type="http",
 *                 scheme="bearer",
 *                 bearerFormat="Sanctum"
 *             )
 *         }
 *     ),
 *     tags={
 *         @OA\Tag(name="Auth", description="Login, logout, recuperación de contraseña"),
 *         @OA\Tag(name="User", description="Perfil de usuario")
 *     }
 * )
 */
class OpenApi
{
}
