# Reglas de Documentación de Código (Obligatorias)

Estas reglas aplican a **todo el código generado o modificado** en el proyecto,
tanto en **backend** como en **frontend**.

La documentación NO es opcional.

---

## Regla General

Todo código nuevo o modificado DEBE:
- Estar correctamente documentado.
- Ser comprensible sin necesidad de inspeccionar su implementación interna.
- Explicar el propósito y la intención, no solo la sintaxis.

Si el código no está documentado, el cambio se considera incompleto.

---

## Regla Obligatoria: Documentación de Clases, Métodos y Propiedades

**TODAS las clases, métodos y propiedades definidas durante la codificación DEBEN estar documentadas.**

Esto aplica a:
- ✅ **Todas las clases** (públicas, privadas, internas)
- ✅ **Todos los métodos** (públicos, privados, protegidos, estáticos)
- ✅ **Todas las propiedades** (públicas, privadas, protegidas, constantes)

**No hay excepciones.** Incluso métodos privados o propiedades internas deben tener documentación que explique su propósito.

---

## Backend (Laravel / PHP)

### Clases
Toda clase DEBE incluir:
- Un comentario PHPDoc descriptivo a nivel de clase.
- La responsabilidad principal de la clase dentro del sistema.
- Ejemplo de uso (si aplica).

### Métodos
Todo método DEBE documentar:
- Qué hace el método.
- El significado de cada parámetro (con `@param`).
- Qué devuelve (con `@return`).
- Posibles excepciones o casos de fallo (con `@throws`, si aplica).

### Propiedades
Toda propiedad DEBE documentar:
- El propósito de la propiedad.
- El tipo de dato esperado.
- Valores posibles o restricciones (si aplica).

Ejemplo (PHPDoc en Laravel):

```php
/**
 * Servicio para gestionar el registro de tareas diarias.
 * 
 * Este servicio maneja la lógica de negocio relacionada con la creación,
 * actualización y consulta de registros de tareas asociados a usuarios y clientes.
 */
class RegistroTareaService
{
    /**
     * ID del usuario autenticado que realiza la operación.
     * 
     * @var int
     */
    private int $usuarioId;

    /**
     * Crea un nuevo registro de tarea para el usuario autenticado.
     * 
     * Valida que la duración sea múltiplo de 15 minutos y que el cliente
     * y tipo de tarea estén activos antes de crear el registro.
     * 
     * @param array $datos Datos del registro de tarea (fecha, cliente_id, tipo_tarea_id, duracion_minutos, etc.)
     * @return RegistroTarea El registro de tarea creado
     * @throws ValidationException Si los datos no son válidos
     * @throws BusinessException Si el cliente o tipo de tarea no están activos
     */
    public function crearRegistroTarea(array $datos): RegistroTarea
    {
        // Implementación...
    }

    /**
     * Obtiene el total de horas trabajadas por un usuario en un rango de fechas.
     * 
     * @param int $usuarioId ID del usuario
     * @param string $fechaInicio Fecha de inicio (formato: Y-m-d)
     * @param string $fechaFin Fecha de fin (formato: Y-m-d)
     * @return float Total de horas trabajadas
     */
    private function calcularTotalHoras(int $usuarioId, string $fechaInicio, string $fechaFin): float
    {
        // Implementación...
    }
}
```

---

## Backend (C# / .NET)

### Clases
Toda clase DEBE incluir:
- Un comentario XML descriptivo a nivel de clase.
- La responsabilidad principal de la clase dentro del sistema.

### Métodos
Todo método DEBE documentar:
- Qué hace el método.
- El significado de cada parámetro.
- Qué devuelve.
- Posibles errores o casos de fallo a nivel de negocio (si aplica).

### Propiedades
Toda propiedad DEBE documentar:
- El propósito de la propiedad.
- El tipo de dato y valores posibles.

Ejemplo (comentarios XML en C#):

```csharp
/// <summary>
/// Crea un nuevo parte de trabajo para el empleado autenticado.
/// </summary>
/// <param name="request">Datos del parte a crear.</param>
/// <returns>El parte creado.</returns>
