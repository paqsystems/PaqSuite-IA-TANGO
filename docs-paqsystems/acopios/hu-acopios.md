# Historias de Usuario - Módulo de Acopios

## Introducción

Este documento contiene el catálogo completo de historias de usuario para el módulo de gestión de acopios. El módulo permite realizar facturas anticipadas a clientes fijando precios de artículos, y posteriormente asociar pedidos a esas facturas, llevando control del cumplimiento total de la factura.

Este documento cubre las siguientes áreas funcionales:
- Configuración de parámetros del módulo
- Definición de facturas de acopio
- Asociación de pedidos a facturas de acopio
- Consulta de saldos de acopios
- Gestión de estados de acopios

---

## Supuestos y Definiciones

### Conceptos Clave

- **Acopio**: Factura anticipada a un cliente donde se fijan precios de artículos. A futuro se van asignando pedidos a esa factura, controlando que no se acopie más de lo facturado.
- **Factura de Acopio**: Factura (GVA12 con T_COMP='FAC') que contiene al menos un artículo cuyo código comienza con el prefijo configurado en parámetros.
- **Estado de Acopio**: 
  - **Abierto (0)**: La factura de acopio tiene saldo disponible para asociar pedidos
  - **Cerrado (1)**: La factura de acopio ya canceló el total facturado (no hay saldo disponible)

### Entidades Principales

- **PQ_ACOPIOS_FACTURAS**: Almacena los datos adicionales de las facturas de acopio (lista de precios, fecha vigencia, descuento, importes, estado).
- **PQ_ACOPIOS_PEDIDOS**: Archiva la asociación entre pedidos y facturas de acopio.
- **GVA12**: Comprobantes (cabecera) - tabla del sistema base.
- **GVA53**: Detalle de Comprobantes - tabla del sistema base.
- **GVA21**: Cabecera de Pedidos - tabla del sistema base.
- **GVA03**: Detalle de Pedidos - tabla del sistema base.
- **GVA14**: Clientes - tabla del sistema base.
- **GVA10**: Lista de Precios - tabla del sistema base.

### Reglas de Negocio Clave

- **Facturas elegibles**: Solo se consideran facturas donde `T_COMP='FAC'` y que tengan al menos un artículo en el detalle (GVA53) cuyo código (`COD_ARTICU`) comience con el prefijo configurado en parámetros.
- **Mismo cliente**: Solo se pueden asociar pedidos a facturas de acopio del mismo cliente.
- **Control de saldo**: Al asociar un pedido, se debe verificar que no supere el saldo disponible de la factura de acopio.
- **Cierre automático**: Cuando se consulta saldos de acopios, se deben marcar como "Cerrado" las facturas que ya cancelaron el total facturado.
- **Valorización de pedidos**: La valorización se calcula por cada artículo como: `precio_neto * cantidad * (1 - descuento/100)`, donde:
  - `cantidad`: la que figura en el renglón del pedido (GVA03)
  - `precio`: el de la lista de precios definida en PQ_ACOPIOS_FACTURAS
  - `descuento`: el definido en PQ_ACOPIOS_FACTURAS
- **Comparativa sin impuestos**: La comparativa de importes entre factura y pedidos se realiza a importe sin impuestos.
- **Operación multi-empresa**: Las tablas PQ_ACOPIOS_FACTURAS y PQ_ACOPIOS_PEDIDOS se operan desde la empresa en curso, pero para obtener facturas y pedidos se deben usar funciones tabulares que traigan información de todas las empresas del grupo empresario configurado.

---

## Épica 1: Configuración de Parámetros

### HU-ACO-001 – Configurar parámetros del módulo de acopios

**Rol:** Administrador del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como administrador del sistema, quiero configurar los parámetros del módulo de acopios (prefijo de artículos y grupo empresario), para que el sistema pueda identificar correctamente las facturas de acopio y operar en modo multi-empresa.

**Criterios de Aceptación:**

1. **Configuración de Prefijo de Artículos:**
   - Debe existir una interfaz para configurar el parámetro `"PrefijoArticulo"` en la tabla `PQ_Parametros_Gral` con Programa = `"Acopios"`.
   - El valor debe ser un string (ejemplo: "AC").
   - El parámetro debe ser obligatorio.
   - Debe validarse que el prefijo no esté vacío.
   - Debe permitir editar el valor del prefijo.

2. **Configuración de Grupo Empresario:**
   - Debe existir una interfaz para configurar el parámetro `"GrupoEmpresario"` en la tabla `PQ_Parametros_Gral` con Programa = `"Acopios"`.
   - El valor debe ser un código válido de un registro de la tabla `Pq_GrupoEmpresario_Cabecera` del diccionario vigente.
   - Debe validarse que el código de grupo empresario exista en la tabla referenciada.
   - Debe permitir editar el valor del grupo empresario.

3. **Persistencia:**
   - Los parámetros deben guardarse en `PQ_Parametros_Gral` con el campo Programa = `"Acopios"`.
   - Debe validarse que ambos parámetros estén configurados antes de permitir usar otros procesos del módulo.

4. **Validaciones:**
   - Si falta algún parámetro, el sistema debe mostrar un mensaje indicando qué parámetros faltan.
   - No se debe permitir eliminar parámetros si hay facturas de acopio registradas.

**Reglas de Negocio:**
- Los parámetros se almacenan en `PQ_Parametros_Gral` con Programa = `"Acopios"`.
- El prefijo de artículos se usa para filtrar facturas elegibles (artículos que comienzan con ese prefijo).
- El grupo empresario se usa para operación multi-empresa (obtener facturas y pedidos de todas las empresas del grupo).

---

## Épica 2: Definición de Facturas de Acopio

### HU-ACO-002 – Listar facturas elegibles para acopio

**Rol:** Usuario del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario del sistema, quiero ver un listado de facturas elegibles para configurar como acopio, para poder seleccionar cuáles configurar.

**Criterios de Aceptación:**

1. **Filtrado de Facturas:**
   - Debe mostrar solo facturas donde `T_COMP='FAC'` (facturas).
   - Debe mostrar solo facturas que tengan al menos un artículo en el detalle (GVA53) cuyo código (`COD_ARTICU`) comience con el prefijo configurado en `"PrefijoArticulo"`.
   - Debe considerar facturas de todas las empresas del grupo empresario configurado (operación multi-empresa).
   - No debe mostrar facturas que ya están configuradas como acopio (existen en PQ_ACOPIOS_FACTURAS).

2. **Información Mostrada:**
   - Tipo de comprobante (T_COMP)
   - Número de comprobante (N_COMP)
   - Código de cliente (COD_CLIENT)
   - Razón social del cliente
   - Fecha de emisión (FECHA_EMIS)
   - Importe total
   - Estado del comprobante

3. **Funcionalidades:**
   - Debe permitir filtrar por cliente.
   - Debe permitir filtrar por rango de fechas.
   - Debe permitir ordenar por fecha de emisión (más recientes primero).
   - Debe mostrar paginación si hay muchos resultados.

**Reglas de Negocio:**
- Solo se consideran facturas (T_COMP='FAC').
- Solo se consideran facturas con artículos que comienzan con el prefijo configurado.
- Se deben obtener facturas de todas las empresas del grupo empresario.

---

### HU-ACO-003 – Configurar factura como acopio

**Rol:** Usuario del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario del sistema, quiero configurar una factura elegible como factura de acopio, asignando lista de precios, fecha de vigencia y porcentaje de descuento, para poder asociar pedidos a esa factura en el futuro.

**Criterios de Aceptación:**

1. **Selección de Factura:**
   - Debe permitir seleccionar una factura del listado de facturas elegibles (HU-ACO-002).
   - Debe mostrar información de la factura seleccionada:
     - Tipo y número de comprobante
     - Cliente
     - Fecha de emisión
     - Importe total
     - Detalle de artículos facturados

2. **Configuración de Datos:**
   - Debe permitir seleccionar una **Lista de Precios** (GVA10) de las listas habilitadas del sistema.
   - Debe permitir ingresar una **Fecha de Vigencia** (fecha hasta la cual es válida la factura de acopio).
   - Debe permitir ingresar un **Porcentaje de Descuento** (numeric 6,2, entre 0 y 100).

3. **Validaciones:**
   - La lista de precios es obligatoria.
   - La fecha de vigencia es obligatoria y debe ser mayor o igual a la fecha actual.
   - El porcentaje de descuento es obligatorio y debe estar entre 0 y 100.
   - No debe permitir configurar la misma factura dos veces (validar que no exista en PQ_ACOPIOS_FACTURAS).

4. **Cálculo de Importes:**
   - Debe calcular y guardar:
     - `Importe_Neto`: Importe neto de la factura sin impuestos (desde GVA12)
     - `Importe_Impuestos`: Importe de impuestos de la factura (desde GVA12)
     - `Importe_Total`: Importe total de la factura (desde GVA12)
   - Debe inicializar `Saldo_Anterior` con el valor de `Importe_Neto`.

5. **Persistencia:**
   - Debe crear un registro en `PQ_ACOPIOS_FACTURAS` con:
     - `t_comp`: Tipo de comprobante de la factura
     - `n_comp`: Número de comprobante de la factura
     - `cod_client`: Código del cliente de la factura
     - `Fecha_vigencia`: Fecha ingresada
     - `Lista_Precios`: ID de la lista de precios seleccionada
     - `Descuento`: Porcentaje ingresado
     - `Importe_Neto`, `Importe_Impuestos`, `Importe_Total`: Calculados desde GVA12
     - `Fecha_Umo_Acopio`: Fecha actual
     - `Saldo_Anterior`: Inicializado con Importe_Neto
     - `Estado`: 0 (Abierto)
   - Debe guardar en la empresa en curso.

6. **Confirmación:**
   - Debe mostrar mensaje de confirmación al guardar exitosamente.
   - Debe permitir volver al listado de facturas elegibles.

**Reglas de Negocio:**
- Una factura solo puede configurarse una vez como acopio.
- El estado inicial siempre es "Abierto" (0).
- El saldo anterior se inicializa con el importe neto de la factura.

---

### HU-ACO-004 – Editar configuración de factura de acopio

**Rol:** Usuario del sistema  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario del sistema, quiero editar la configuración de una factura de acopio (lista de precios, fecha de vigencia, descuento), para corregir errores o actualizar valores.

**Criterios de Aceptación:**

1. **Selección de Factura de Acopio:**
   - Debe permitir seleccionar una factura de acopio existente (de PQ_ACOPIOS_FACTURAS).
   - Debe mostrar la información actual de la factura de acopio.

2. **Edición de Datos:**
   - Debe permitir modificar:
     - Lista de Precios
     - Fecha de Vigencia
     - Porcentaje de Descuento
   - No debe permitir modificar:
     - Tipo y número de comprobante
     - Cliente
     - Importes (calculados desde GVA12)

3. **Validaciones:**
   - Las mismas validaciones que en HU-ACO-003.
   - No debe permitir editar si la factura de acopio está cerrada (Estado = 1).

4. **Actualización:**
   - Debe actualizar `Fecha_Umo_Acopio` con la fecha actual al guardar.
   - Debe mostrar mensaje de confirmación al actualizar.

**Reglas de Negocio:**
- Solo se pueden editar facturas de acopio abiertas.
- La fecha de última modificación se actualiza automáticamente.

---

## Épica 3: Asociación de Pedidos a Acopios

### HU-ACO-005 – Listar pedidos disponibles para asociar a acopio

**Rol:** Usuario del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario del sistema, quiero ver un listado de pedidos disponibles para asociar a facturas de acopio, para poder seleccionar cuáles asociar.

**Criterios de Aceptación:**

1. **Filtrado de Pedidos:**
   - Debe mostrar pedidos (GVA21) que:
     - No estén anulados (ESTADO != 5)
     - No estén ya asociados a una factura de acopio (no existen en PQ_ACOPIOS_PEDIDOS)
     - Pertenecen a clientes que tienen facturas de acopio abiertas
   - Debe considerar pedidos de todas las empresas del grupo empresario configurado.

2. **Información Mostrada:**
   - Talonario del pedido (TALON_PED)
   - Número de pedido (NRO_PEDIDO)
   - Código de cliente (COD_CLIENT)
   - Razón social del cliente
   - Fecha del pedido (FECHA_PEDI)
   - Fecha de entrega prevista (FECHA_ENTR)
   - Total del pedido
   - Estado del pedido

3. **Funcionalidades:**
   - Debe permitir filtrar por cliente.
   - Debe permitir filtrar por rango de fechas.
   - Debe permitir ordenar por fecha del pedido (más recientes primero).
   - Debe mostrar paginación si hay muchos resultados.

**Reglas de Negocio:**
- Solo se muestran pedidos de clientes que tienen facturas de acopio abiertas.
- Se deben obtener pedidos de todas las empresas del grupo empresario.

---

### HU-ACO-006 – Asociar pedido a factura de acopio

**Rol:** Usuario del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario del sistema, quiero asociar un pedido a una factura de acopio del mismo cliente, para que el pedido consuma saldo de la factura de acopio.

**Criterios de Aceptación:**

1. **Selección de Pedido:**
   - Debe permitir seleccionar un pedido del listado de pedidos disponibles (HU-ACO-005).
   - Debe mostrar información del pedido seleccionado:
     - Talonario y número de pedido
     - Cliente
     - Fecha del pedido
     - Detalle de artículos del pedido (GVA03)

2. **Selección de Factura de Acopio:**
   - Debe mostrar solo facturas de acopio abiertas (Estado = 0) del mismo cliente que el pedido.
   - Debe mostrar información de cada factura de acopio:
     - Tipo y número de comprobante
     - Fecha de vigencia
     - Lista de precios asignada
     - Descuento configurado
     - Saldo disponible (calculado)
   - Debe permitir seleccionar una factura de acopio.

3. **Cálculo de Valorización del Pedido:**
   - Debe calcular la valorización del pedido según la fórmula:
     ```
     Por cada artículo del pedido (GVA03):
     Valorización = precio_neto * cantidad * (1 - descuento/100)
     ```
   - Donde:
     - `cantidad`: CANT_PEDID del renglón del pedido (GVA03)
     - `precio_neto`: Precio de la lista de precios configurada en PQ_ACOPIOS_FACTURAS para el artículo correspondiente
     - `descuento`: Descuento configurado en PQ_ACOPIOS_FACTURAS
   - Debe mostrar el total valorizado del pedido.

4. **Validación de Saldo:**
   - Debe calcular el saldo disponible de la factura de acopio:
     ```
     Saldo_Disponible = Saldo_Anterior - Suma de valorizaciones de pedidos ya asociados
     ```
   - Debe validar que el total valorizado del pedido no supere el saldo disponible.
   - Si supera el saldo, debe mostrar un mensaje de error indicando el saldo disponible y no permitir la asociación.

5. **Persistencia:**
   - Si la validación es exitosa, debe crear un registro en `PQ_ACOPIOS_PEDIDOS` con:
     - `t_comp`: Tipo de comprobante de la factura de acopio seleccionada
     - `n_comp`: Número de comprobante de la factura de acopio seleccionada
     - `Talon_ped`: Talonario del pedido seleccionado
     - `Nro_Pedido`: Número del pedido seleccionado
     - `cod_client`: Código del cliente (debe coincidir entre pedido y factura)
     - `Estado`: Valor inicial (definir según reglas de negocio)
   - Debe guardar en la empresa en curso.

6. **Confirmación:**
   - Debe mostrar mensaje de confirmación al asociar exitosamente.
   - Debe mostrar el saldo restante de la factura de acopio.

**Reglas de Negocio:**
- Solo se pueden asociar pedidos a facturas de acopio del mismo cliente.
- El pedido no puede superar el saldo disponible de la factura de acopio.
- La valorización se calcula usando la lista de precios y descuento configurados en la factura de acopio.
- La comparativa de importes se realiza sin impuestos.

---

### HU-ACO-007 – Desasociar pedido de factura de acopio

**Rol:** Usuario del sistema  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario del sistema, quiero desasociar un pedido de una factura de acopio, para corregir asociaciones incorrectas o liberar saldo.

**Criterios de Aceptación:**

1. **Selección de Asociación:**
   - Debe permitir seleccionar una asociación existente (de PQ_ACOPIOS_PEDIDOS).
   - Debe mostrar información de la asociación:
     - Factura de acopio (tipo y número)
     - Pedido (talonario y número)
     - Cliente
     - Fecha de asociación

2. **Validaciones:**
   - No debe permitir desasociar si la factura de acopio está cerrada (Estado = 1).
   - Debe solicitar confirmación antes de eliminar la asociación.

3. **Eliminación:**
   - Debe eliminar el registro de `PQ_ACOPIOS_PEDIDOS`.
   - Debe mostrar mensaje de confirmación.
   - Debe actualizar el saldo disponible de la factura de acopio (liberar el saldo consumido por ese pedido).

**Reglas de Negocio:**
- Solo se pueden desasociar pedidos de facturas de acopio abiertas.
- Al desasociar, se libera el saldo consumido por ese pedido.

---

## Épica 4: Consulta de Saldos de Acopios

### HU-ACO-008 – Consultar saldos de acopios

**Rol:** Usuario del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario del sistema, quiero consultar las facturas de acopios con sus pedidos asociados y saldos disponibles, para poder ver el estado de cumplimiento de cada acopio.

**Criterios de Aceptación:**

1. **Listado de Facturas de Acopio:**
   - Debe mostrar todas las facturas de acopio (de PQ_ACOPIOS_FACTURAS) de la empresa en curso.
   - Debe mostrar información de cada factura:
     - Tipo y número de comprobante
     - Cliente (código y razón social)
     - Fecha de emisión
     - Fecha de vigencia
     - Lista de precios asignada
     - Descuento configurado
     - Importe neto, impuestos y total
     - Estado (Abierto/Cerrado)
     - Saldo disponible (calculado)

2. **Cálculo de Saldo Disponible:**
   - Debe calcular el saldo disponible para cada factura de acopio:
     ```
     Saldo_Disponible = Importe_Neto - Suma de valorizaciones de pedidos asociados
     ```
   - La valorización de cada pedido asociado se calcula según la fórmula definida en HU-ACO-006.

3. **Cierre Automático:**
   - Al consultar, debe verificar si alguna factura de acopio tiene saldo disponible <= 0.
   - Si el saldo disponible <= 0, debe actualizar el campo `Estado` a 1 (Cerrado) en `PQ_ACOPIOS_FACTURAS`.
   - Debe actualizar `Fecha_Umo_Acopio` con la fecha actual.

4. **Detalle de Pedidos Asociados:**
   - Debe permitir expandir cada factura de acopio para ver los pedidos asociados.
   - Para cada pedido asociado debe mostrar:
     - Talonario y número de pedido
     - Fecha del pedido
     - Valorización del pedido (calculada)
     - Estado del pedido

5. **Filtros y Búsqueda:**
   - Debe permitir filtrar por cliente.
   - Debe permitir filtrar por estado (Abierto/Cerrado).
   - Debe permitir filtrar por rango de fechas de emisión.
   - Debe permitir buscar por número de comprobante.

6. **Ordenamiento:**
   - Debe permitir ordenar por fecha de emisión, cliente, estado, saldo disponible.

**Reglas de Negocio:**
- El saldo disponible se calcula restando las valorizaciones de pedidos asociados al importe neto.
- Las facturas con saldo <= 0 se marcan automáticamente como cerradas.
- La valorización de pedidos se calcula según la fórmula definida.

---

### HU-ACO-009 – Ver detalle de factura de acopio con pedidos asociados

**Rol:** Usuario del sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como usuario del sistema, quiero ver el detalle completo de una factura de acopio con todos sus pedidos asociados y el cálculo de saldos por artículo, para analizar el cumplimiento del acopio.

**Criterios de Aceptación:**

1. **Información de la Factura de Acopio:**
   - Debe mostrar toda la información de la factura de acopio:
     - Tipo y número de comprobante
     - Cliente completo
     - Fecha de emisión y fecha de vigencia
     - Lista de precios asignada
     - Descuento configurado
     - Importes (neto, impuestos, total)
     - Estado (Abierto/Cerrado)
     - Saldo disponible total

2. **Detalle de Artículos Facturados:**
   - Debe mostrar el detalle de artículos de la factura (GVA53):
     - Código de artículo
     - Descripción del artículo
     - Cantidad facturada
     - Precio unitario
     - Importe del renglón

3. **Pedidos Asociados:**
   - Debe mostrar todos los pedidos asociados a esta factura de acopio (de PQ_ACOPIOS_PEDIDOS).
   - Para cada pedido debe mostrar:
     - Talonario y número de pedido
     - Fecha del pedido
     - Cliente (debe coincidir con la factura)
     - Valorización total del pedido
     - Detalle de artículos del pedido con valorización

4. **Cálculo de Saldos por Artículo:**
   - Debe calcular el saldo disponible por artículo:
     ```
     Por cada artículo facturado:
     Saldo_Artículo = Cantidad_Facturada - Suma de cantidades en pedidos asociados
     ```
   - Debe mostrar el saldo disponible por artículo.

5. **Resumen de Consumo:**
   - Debe mostrar un resumen:
     - Importe neto facturado
     - Total valorizado de pedidos asociados
     - Saldo disponible (en importe)
     - Porcentaje consumido

**Reglas de Negocio:**
- El saldo se calcula considerando las cantidades y valorizaciones de los pedidos asociados.
- La valorización de pedidos usa la lista de precios y descuento de la factura de acopio.

---

### HU-ACO-010 – Exportar consulta de saldos de acopios

**Rol:** Usuario del sistema  
**Clasificación:** SHOULD-HAVE  
**Historia:** Como usuario del sistema, quiero exportar la consulta de saldos de acopios a Excel, para poder analizar los datos fuera del sistema o generar reportes.

**Criterios de Aceptación:**

1. **Formato de Exportación:**
   - Debe exportar a formato Excel (.xlsx).
   - Debe incluir todas las columnas visibles en el listado.

2. **Contenido:**
   - Debe incluir:
     - Información de cada factura de acopio
     - Saldo disponible de cada factura
     - Estado (Abierto/Cerrado)
     - Lista de pedidos asociados (en hojas separadas o expandida)

3. **Filtros Aplicados:**
   - Debe respetar los filtros aplicados en la consulta al exportar.

**Reglas de Negocio:**
- La exportación debe reflejar exactamente lo que se muestra en pantalla con los filtros aplicados.

---

## Épica 5: Validaciones y Reglas de Negocio

### HU-ACO-011 – Validar que no se supere el saldo disponible al asociar pedido

**Rol:** Sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como sistema, quiero validar que al asociar un pedido a una factura de acopio no se supere el saldo disponible, para mantener la integridad de los datos y evitar acopios excedidos.

**Criterios de Aceptación:**

1. **Cálculo de Saldo Disponible:**
   - Debe calcular correctamente el saldo disponible antes de permitir la asociación.
   - Debe considerar todos los pedidos ya asociados a la factura de acopio.

2. **Validación:**
   - Si el total valorizado del pedido supera el saldo disponible, debe:
     - Mostrar un mensaje de error claro indicando el saldo disponible.
     - No permitir la asociación.
     - Mostrar el importe que se intenta asociar vs el saldo disponible.

3. **Mensajes de Error:**
   - Debe mostrar mensajes descriptivos:
     - "El pedido supera el saldo disponible de la factura de acopio"
     - "Saldo disponible: $X.XX | Intento de asociar: $Y.YY"

**Reglas de Negocio:**
- El saldo disponible nunca puede ser negativo.
- La validación se realiza antes de persistir la asociación.

---

### HU-ACO-012 – Validar que pedido y factura de acopio pertenezcan al mismo cliente

**Rol:** Sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como sistema, quiero validar que un pedido solo pueda asociarse a una factura de acopio del mismo cliente, para mantener la integridad de los datos.

**Criterios de Aceptación:**

1. **Validación de Cliente:**
   - Al intentar asociar un pedido a una factura de acopio, debe validar que ambos pertenezcan al mismo cliente (mismo `cod_client`).
   - Si los clientes no coinciden, debe mostrar un mensaje de error y no permitir la asociación.

2. **Filtrado en Selección:**
   - Al mostrar facturas de acopio disponibles para asociar, solo debe mostrar las del mismo cliente que el pedido seleccionado.

**Reglas de Negocio:**
- Solo se pueden asociar pedidos a facturas de acopio del mismo cliente.
- Esta validación es obligatoria y no puede omitirse.

---

### HU-ACO-013 – Validar que la factura de acopio esté abierta antes de asociar pedido

**Rol:** Sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como sistema, quiero validar que solo se puedan asociar pedidos a facturas de acopio abiertas, para evitar modificar acopios ya cerrados.

**Criterios de Aceptación:**

1. **Validación de Estado:**
   - Al intentar asociar un pedido, debe validar que la factura de acopio tenga `Estado = 0` (Abierto).
   - Si la factura está cerrada (Estado = 1), debe mostrar un mensaje de error y no permitir la asociación.

2. **Filtrado en Selección:**
   - Al mostrar facturas de acopio disponibles para asociar, solo debe mostrar las que están abiertas.

**Reglas de Negocio:**
- Solo se pueden asociar pedidos a facturas de acopio abiertas.
- Las facturas cerradas no pueden recibir nuevas asociaciones.

---

## Épica 6: Operación Multi-Empresa

### HU-ACO-014 – Obtener facturas de todas las empresas del grupo empresario

**Rol:** Sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como sistema, quiero obtener facturas de todas las empresas del grupo empresario configurado, para permitir la operación multi-empresa del módulo de acopios.

**Criterios de Aceptación:**

1. **Función Tabular:**
   - Debe usar funciones tabulares que consulten todas las empresas del grupo empresario configurado en parámetros.
   - Debe obtener facturas (GVA12) donde `T_COMP='FAC'` de todas las empresas del grupo.

2. **Filtrado por Prefijo:**
   - Debe filtrar solo facturas que tengan artículos con el prefijo configurado en el detalle (GVA53).

3. **Consistencia de Datos:**
   - Debe mantener la consistencia de datos entre empresas.
   - Debe identificar correctamente a qué empresa pertenece cada factura.

**Reglas de Negocio:**
- Las tablas PQ_ACOPIOS_FACTURAS y PQ_ACOPIOS_PEDIDOS se operan desde la empresa en curso.
- Para obtener facturas y pedidos, se deben usar funciones tabulares que consulten todas las empresas del grupo empresario.

---

### HU-ACO-015 – Obtener pedidos de todas las empresas del grupo empresario

**Rol:** Sistema  
**Clasificación:** MUST-HAVE  
**Historia:** Como sistema, quiero obtener pedidos de todas las empresas del grupo empresario configurado, para permitir asociar pedidos de cualquier empresa del grupo a facturas de acopio.

**Criterios de Aceptación:**

1. **Función Tabular:**
   - Debe usar funciones tabulares que consulten todas las empresas del grupo empresario configurado.
   - Debe obtener pedidos (GVA21) de todas las empresas del grupo.

2. **Filtrado:**
   - Debe filtrar solo pedidos no anulados (ESTADO != 5).
   - Debe filtrar solo pedidos de clientes que tienen facturas de acopio abiertas.

3. **Consistencia de Datos:**
   - Debe mantener la consistencia de datos entre empresas.
   - Debe identificar correctamente a qué empresa pertenece cada pedido.

**Reglas de Negocio:**
- Los pedidos se obtienen de todas las empresas del grupo empresario para permitir asociaciones multi-empresa.

---

## Resumen de Historias

### Clasificación por Prioridad

**MUST-HAVE (12 historias):**
- HU-ACO-001: Configurar parámetros del módulo
- HU-ACO-002: Listar facturas elegibles
- HU-ACO-003: Configurar factura como acopio
- HU-ACO-005: Listar pedidos disponibles
- HU-ACO-006: Asociar pedido a factura de acopio
- HU-ACO-008: Consultar saldos de acopios
- HU-ACO-009: Ver detalle de factura de acopio
- HU-ACO-011: Validar saldo disponible
- HU-ACO-012: Validar mismo cliente
- HU-ACO-013: Validar factura abierta
- HU-ACO-014: Obtener facturas multi-empresa
- HU-ACO-015: Obtener pedidos multi-empresa

**SHOULD-HAVE (3 historias):**
- HU-ACO-004: Editar configuración de factura de acopio
- HU-ACO-007: Desasociar pedido de factura de acopio
- HU-ACO-010: Exportar consulta de saldos

**Total: 15 historias de usuario**

---

## Flujo End-to-End Prioritario

El flujo E2E prioritario del módulo de acopios es:

1. **Configurar parámetros** (HU-ACO-001)
2. **Listar y configurar facturas de acopio** (HU-ACO-002, HU-ACO-003)
3. **Listar y asociar pedidos** (HU-ACO-005, HU-ACO-006)
4. **Consultar saldos de acopios** (HU-ACO-008, HU-ACO-009)

Este flujo permite:
- Configurar el módulo
- Definir facturas de acopio
- Asociar pedidos a facturas de acopio
- Consultar el estado y saldos de los acopios

---

## Notas Técnicas

### Funciones Tabulares Multi-Empresa

Para implementar la operación multi-empresa, se deben crear funciones tabulares que:
- Consulten todas las empresas del grupo empresario configurado
- Retornen facturas (GVA12) y pedidos (GVA21) de todas las empresas
- Mantengan la identificación de la empresa origen

### Cálculo de Valorización

La valorización de pedidos se calcula usando:
- Lista de precios configurada en PQ_ACOPIOS_FACTURAS
- Descuento configurado en PQ_ACOPIOS_FACTURAS
- Cantidad del renglón del pedido (GVA03)

Fórmula: `precio_neto * cantidad * (1 - descuento/100)`

### Cierre Automático

El cierre automático se realiza cuando:
- Saldo disponible <= 0
- Se actualiza Estado = 1 (Cerrado) en PQ_ACOPIOS_FACTURAS
- Se actualiza Fecha_Umo_Acopio con la fecha actual
