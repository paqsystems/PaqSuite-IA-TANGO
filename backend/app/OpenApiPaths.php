<?php

declare(strict_types=1);

namespace App;

/**
 * Paths de la API v1 para OpenAPI.
 *
 * @OA\PathItem(path="/api/v1/auth/login", post=@OA\Post(operationId="authLogin", tags={"Auth"}, summary="Login", responses={@OA\Response(response=200, description="OK")}))
 * @OA\PathItem(path="/api/v1/auth/forgot-password", post=@OA\Post(operationId="authForgotPassword", tags={"Auth"}, summary="Solicitar recuperación de contraseña", responses={@OA\Response(response=200, description="OK")}))
 * @OA\PathItem(path="/api/v1/auth/reset-password", post=@OA\Post(operationId="authResetPassword", tags={"Auth"}, summary="Restablecer contraseña con token", responses={@OA\Response(response=200, description="OK")}))
 * @OA\PathItem(path="/api/v1/auth/logout", post=@OA\Post(operationId="authLogout", tags={"Auth"}, summary="Cerrar sesión", security={{ "bearerAuth": {} }}, responses={@OA\Response(response=200, description="OK")}))
 * @OA\PathItem(path="/api/v1/auth/change-password", post=@OA\Post(operationId="authChangePassword", tags={"Auth"}, summary="Cambiar contraseña", security={{ "bearerAuth": {} }}, responses={@OA\Response(response=200, description="OK")}))
 * @OA\PathItem(path="/api/v1/user", get=@OA\Get(operationId="userCurrent", tags={"User"}, summary="Usuario actual", security={{ "bearerAuth": {} }}, responses={@OA\Response(response=200, description="OK")}))
 * @OA\PathItem(path="/api/v1/user/profile", get=@OA\Get(operationId="userProfileShow", tags={"User"}, summary="Ver perfil", security={{ "bearerAuth": {} }}, responses={@OA\Response(response=200, description="OK")}), put=@OA\Put(operationId="userProfileUpdate", tags={"User"}, summary="Actualizar perfil", security={{ "bearerAuth": {} }}, responses={@OA\Response(response=200, description="OK")}))
 */
class OpenApiPaths
{
}
