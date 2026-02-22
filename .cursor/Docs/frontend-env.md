# Documentación: frontend/.env

## Descripción
Archivo de variables de entorno para el frontend de React/Vite.

## Ubicación
`frontend/.env`

## Variables

| Variable | Descripción | Valor por defecto |
|----------|-------------|-------------------|
| `VITE_API_URL` | URL base del backend API | `http://localhost:8000/api` |

## Uso en código

```typescript
// En cualquier archivo del frontend
const apiUrl = import.meta.env.VITE_API_URL;
```

## Archivos relacionados

- `frontend/.env` - Variables de entorno (NO se sube al repo)
- `frontend/.env.example` - Plantilla de ejemplo (SÍ se sube al repo)
- `frontend/.gitignore` - Ignora `.env`

## Notas

- En Vite, solo las variables que comienzan con `VITE_` están disponibles en el código del cliente
- El archivo `.env` NO debe subirse al repositorio (contiene configuración local)
- Solo `.env.example` se sube como referencia

## TR relacionada
- TR-001(MH)-login-de-empleado.md
