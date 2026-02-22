# Contexto - Módulo de Acopios

## Definición

Este módulo tiene por objetivo llevar la gestión de **"Acopios"** de venta de una empresa.

Esto significa que se realiza una **factura anticipada** a un cliente fijando los precios de los artículos y a futuro se van asignando los pedidos que se van generando a ese cliente por ese acopio, llevando control del cumplimiento total de la factura (que no se acopie más de lo facturado, o poder consultar el pendiente disponible a consumir).

---

## Reglas de Negocio

### Facturas de Acopio

- Sólo se considera como facturas los registros de la tabla **Comprobantes (GVA12)** donde el campo `T_COMP='FAC'`
- Se procesan solo las facturas que tengan facturado un artículo que comience con el prefijo que figura en el parámetro **"PrefijoArtículo"**

### Datos Adicionales

- Los datos adicionales a definir por una factura (proceso 2) se guardan en una tabla **PQ_ACOPIOS_FACTURAS**, además de un campo booleano `ESTADO` (abierto o cerrado)

### Asociación de Pedidos

- Solo se pueden asociar pedidos a facturas de acopio del **mismo cliente**
- Se archiva la asociación en la tabla **PQ_ACOPIOS_PEDIDOS**

### Cierre Automático

- Cuando se hace la consulta de acopios, aprovechar y marcar como **"Cerrado"** las facturas que ya cancelaron el total facturado

### Valorización de Pedidos

La valorización de un pedido asociado a un acopio es, por cada artículo:

```
Valorización = precio_neto * cantidad * (1 - descuento/100)
```

Donde:
- **cantidad**: la que figura en el renglón del pedido
- **precio**: la de la lista de precios definida en el proceso 2 (archivo PQ_ACOPIOS_FACTURAS)
- **descuento**: el definido en el proceso 2 (archivo PQ_ACOPIOS_FACTURAS)

### Comparativa de Importes

- La comparativa de importes entre factura y pedidos se realiza a **importe sin impuestos**

### Operación Multi-Empresa

- Las tablas **PQ_ACOPIOS_FACTURAS** y **PQ_ACOPIOS_PEDIDOS** se operan desde la empresa en curso
- Para obtener las facturas y los pedidos, hay que trabajar funciones tabulares que traigan información de **todas las empresas** que figuran en la clave de parámetros **"GrupoEmpresario"**

### Parámetros

- El nombre del campo Programa de **PQ_Parametros_Gral** donde se almacenarán los parámetros se llama **"Acopios"**

---

## Procesos

Para esto se confeccionan **4 procesos**:

### 1. Parámetros

Definir ciertas constantes a ser utilizados por el módulo:

- **Prefijo de Artículos**: 
  - Clave: `"PrefijoArticulo"`
  - Valor: `string`
  
- **Grupo Empresario**: 
  - Clave: `"GrupoEmpresario"`
  - Valor: código de un registro de la tabla `"Pq_GrupoEmpresario_Cabecera"` del diccionario vigente

### 2. Definir Facturas de Acopios

Obtener las facturas en cuyo detalle se facturó algún artículo con un prefijo determinado (fijado en el parámetro `"PrefijoArticulo"`, por ejemplo: `"AC"`), y asignar tres valores necesarios para el acopio:

- **Lista de precios**
- **Fecha vigencia**
- **Porcentaje descuento**

### 3. Asociar Pedidos

Seleccionar un pedido de venta, ver si ese cliente tiene facturas de acopios pendientes, y asociarla a una de ellas, verificando según los cálculos correspondientes que **no supere el pendiente de acopio**.

### 4. Saldos de Acopios

Poder ver las facturas de acopios, los pedidos asociados a cada una, con el detalle de artículo y el **saldo disponible** para seguir acopiando.

---

## Tablas Involucradas

### Tablas del Sistema Base

- **GVA12**: Comprobantes (cabecera)
- **GVA53**: Detalle de Comprobantes
- **GVA21**: Cabecera de Pedidos
- **GVA03**: Detalle de Pedidos
- **GVA14**: Clientes
- **GVA10**: Lista de Precios

### Tablas del Módulo de Acopios

- **PQ_ACOPIOS_FACTURAS**: Almacena los datos adicionales de las facturas de acopio
  - Campo `ESTADO`: booleano (abierto/cerrado)
  - Lista de precios
  - Fecha vigencia
  - Porcentaje descuento

- **PQ_ACOPIOS_PEDIDOS**: Archiva la asociación entre pedidos y facturas de acopio

### Tablas de Parámetros

- **PQ_Parametros_Gral**: Almacena los parámetros del módulo en el campo Programa `"Acopios"`
- **Pq_GrupoEmpresario_Cabecera**: Define el grupo de empresas para operación multi-empresa
