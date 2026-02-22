# Test Integral: Exportación a Excel

## Descripción

Test de integración de la funcionalidad de exportación a Excel desde las consultas.

## Objetivo

Verificar que la exportación a Excel funcione correctamente, generando archivo con datos correctos.

## Componentes Involucrados

- Botón de exportar (`ExportButton`)
- Servicio de exportación (`ExportService`)
- Generador de Excel (`ExcelGenerator`)

## Flujo de Prueba

### Paso 1: Preparación de Datos
1. Aplicar filtros en consulta
2. Cargar resultados
3. Verificar que hay datos para exportar

### Paso 2: Exportación
1. Hacer clic en botón "Exportar a Excel"
2. Verificar que se genere el archivo
3. Verificar descarga del archivo

### Paso 3: Validación de Contenido
1. Abrir archivo Excel descargado
2. Verificar que contenga los datos correctos
3. Verificar formato de columnas
4. Verificar formato de horas (decimal)

## Casos de Prueba

### Caso 1: Exportación de Consulta Simple
- **Precondición:** Consulta con resultados
- **Acción:** Hacer clic en "Exportar a Excel"
- **Resultado Esperado:**
  - Archivo Excel descargado
  - Nombre de archivo descriptivo
  - Datos correctos en el archivo

### Caso 2: Exportación de Consulta Agrupada
- **Precondición:** Consulta agrupada con resultados
- **Acción:** Exportar
- **Resultado Esperado:**
  - Archivo con estructura de agrupación
  - Totales incluidos
  - Detalles expandidos incluidos

### Caso 3: Exportación sin Resultados
- **Precondición:** Consulta sin resultados
- **Acción:** Intentar exportar
- **Resultado Esperado:**
  - Mensaje indicando que no hay datos
  - No se genera archivo

### Caso 4: Formato de Horas en Excel
- **Precondición:** Exportación con datos de horas
- **Acción:** Abrir archivo Excel
- **Resultado Esperado:**
  - Horas en formato decimal
  - Columnas formateadas correctamente
  - Fórmulas si aplican

### Caso 5: Exportación de Gran Volumen
- **Precondición:** Consulta con muchos resultados
- **Acción:** Exportar
- **Resultado Esperado:**
  - Archivo generado correctamente
  - Todos los datos incluidos
  - Sin errores de memoria

## Validaciones

- Archivo se genera correctamente
- Datos exportados son correctos
- Formato de horas decimal preservado
- Manejo de casos sin datos
- Performance con grandes volúmenes

