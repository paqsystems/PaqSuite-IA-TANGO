# Diseños de Base de Datos – PAQ Systems

Carpeta de documentación de diseños de tablas para el proyecto PAQ Systems.

**Propósito:** Centralizar todos los diseños de tablas, esquemas y documentación de base de datos generados en el contexto del proyecto para trabajar con las bases de datos de TANGO.

---

## Glosario de tablas (nombre técnico ↔ conceptual)

**[_glosario-tablas.md](_glosario-tablas.md)** — Referencia central que asocia cada nombre técnico/mnemónico (CPA04, GVA12, STA11, etc.) con su **nombre conceptual** (Comprobantes de compra, Comprobantes de venta, Artículos, etc.).

Úsalo cuando:
- Definas requisitos con nombres funcionales; el glosario indica la tabla técnica.
- Revises esquemas; el glosario explica el rol de cada tabla.

Fuentes: [Tablas Compras](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablascompras_oper/) | [Stock](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablastock_oper/) | [Tesorería](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablastesoreria_oper/) | [Ventas](https://ayudas.axoft.com/25ar/documentos/operacion/tablas_oper/tablasventas_oper/)

---

## Contenido

| Archivo | Descripción |
|---------|-------------|
| [tesoreria-contable.md](tesoreria-contable.md) | Scripts CREATE de tablas ASIENTO_COMPROBANTE_SB, ASIENTO_SB, AUXILIAR_ASIENTO_SB, SUBAUXILIAR_ASIENTO_SB |
| [ventas-contable.md](ventas-contable.md) | Scripts CREATE de tablas ASIENTO_COMPROBANTE_GV, ASIENTO_GV, AUXILIAR_ASIENTO_GV, SUBAUXILIAR_ASIENTO_GV |
| [compras-contable.md](compras-contable.md) | Scripts CREATE de tablas ASIENTO_COMPROBANTE_CP, ASIENTO_CP, AUXILIAR_ASIENTO_CP, SUBAUXILIAR_ASIENTO_CP |
| [ventas.md](ventas.md) | Diseño y relaciones del módulo ventas (GVA21, GVA03, GVA12, GVA12DE, etc.) |
| [tesoreria.md](tesoreria.md) | Diseño y relaciones del módulo tesorería (SBA04, SBA05, SBA01, SBA02, BANCO, etc.) |
| [stock.md](stock.md) | Diseño y relaciones del módulo stock (STA14, STA20, STA09, STA19, STA10, STA22) |
| [clientes.md](clientes.md) | Diseño y CREATE del módulo clientes (GVA14, GVA27, DIRECCION_ENTREGA, GVA01, GVA10, GVA05, GVA18, GVA23, GVA24, CATEGORIA_IVA, etc.) |
| [articulos.md](articulos.md) | Diseño y CREATE del módulo artículos (STA11, CPA14, GVA41, MEDIDA, STA32, STA33) |
| [compras-proveedores.md](compras-proveedores.md) | Diseño y CREATE del módulo compras/proveedores (CPA01, CPA08, CPA14, CONDICION_COMPRA, GVA151, PROVINCIA, etc.) |
| [compras.md](compras.md) | Diseño y CREATE del módulo compras/comprobantes (CPA04, CPA46, CPA47, CPA05, CPA54, CPA18, CPA20, CPA63, etc.) |
