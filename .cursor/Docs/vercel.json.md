# Documentación: frontend/vercel.json

## Propósito
Configuración de Vercel para el frontend SPA (React + React Router). Las `rewrites` hacen que todas las rutas (`/login`, `/dashboard`, etc.) sirvan `index.html`, permitiendo que React Router maneje el enrutamiento del lado del cliente.

## Problema que resuelve
Sin este archivo, al acceder directamente a https://lidrproyectofinal.vercel.app/login (o refrescar en esa URL), Vercel devolvía 404 porque no existe un archivo físico en `/login`.

## Ubicación
`frontend/vercel.json`

## Uso
Se aplica automáticamente cuando Vercel tiene configurado "Root Directory" = `frontend`. Tras añadir este archivo, hay que hacer push y redeploy.
