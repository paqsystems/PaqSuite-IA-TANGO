# MVP Entregables

## Reglas para el Desarrollo del MVP

### Estructura del Proyecto

- Mantener separación clara entre los diferentes frontends y el backend
- Seguir estructura de carpetas establecida
- Documentar cada componente importante

### Código

- Usar camelCase para variables, propiedades, métodos y funciones
- **Documentar TODAS las clases, métodos y propiedades** (obligatorio, sin excepciones)
  - Ver reglas detalladas en: `specs/governance/code-documentation-rules.md`
- Seguir principios SOLID
- Mantener funciones pequeñas y enfocadas

### Evitar Duplicidad de Código (DRY)

**Regla fundamental:** En toda la programación, tanto back-end como front-end, evitar la duplicidad de código.

**Principios:**
- Generar métodos o funciones apropiadas para su reutilización
- Si se detecta que un código se puede incorporar a un método existente agregando algún/os parámetro/s y modificando la codificación del mismo, **proponerlo antes de realizarlo**
- Priorizar DRY (Don't Repeat Yourself) sobre duplicación
- Crear utilidades compartidas cuando el código se repite en múltiples lugares

**Ejemplos de aplicación:**
- Validaciones comunes → Funciones de validación reutilizables
- Transformaciones de datos → Utilidades de formateo
- Llamadas API similares → Servicios base con parámetros
- Lógica de negocio repetida → Métodos compartidos en servicios/modelos

**Proceso:**
1. Antes de duplicar código, verificar si existe una función/método similar
2. Si existe, evaluar si se puede extender con parámetros
3. Si se puede extender, **proponer la modificación antes de implementarla**
4. Si no existe, crear una función/método reutilizable desde el inicio

### Testing

- Escribir tests para funcionalidades críticas
- Mantener cobertura mínima del 70%
- Tests deben ser rápidos y determinísticos

### Integraciones

- Validar todas las integraciones con servicios externos
- Manejar errores de forma apropiada
- Implementar retry logic cuando sea necesario
- Usar variables de entorno para configuración

### Seguridad

- No commitear credenciales
- Validar todas las entradas de usuario
- Sanitizar queries SQL
- Usar HTTPS en producción

### Documentación

- Mantener README actualizado
- Documentar APIs importantes
- Actualizar documentación en `docs/` conforme avance el proyecto
- Documentar decisiones arquitectónicas importantes

### Git

- No hacer commit ni push sin autorización previa
- Usar mensajes de commit descriptivos
- Crear branches para features importantes
- Revisar cambios antes de mergear

### MCP Servers

- Validar configuración de MCP servers antes de usar
- Manejar errores de conexión apropiadamente
- Documentar uso de cada MCP server

### Entregables Mínimos

- [ ] Backend funcional con al menos una API endpoint
- [ ] Frontend funcional con al menos una página
- [ ] Integración con al menos un MCP server funcionando
- [ ] Tests básicos implementados
- [ ] Documentación básica completa
- [ ] Docker Compose funcionando para desarrollo local

### Criterios de Calidad

- Código sin errores de linting críticos
- Tests pasando
- Documentación actualizada
- Sin vulnerabilidades de seguridad conocidas

### Notas

- Estas reglas pueden actualizarse conforme avance el proyecto
- Priorizar funcionalidad sobre perfección
- Iterar y mejorar continuamente
