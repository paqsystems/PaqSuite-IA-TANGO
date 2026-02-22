# Plugin AWS en Cursor Marketplace – Documentación

Resumen de capacidades del plugin de AWS en el marketplace de Cursor y alternativas para automatizar el deploy del backend (Forge) y frontend en distintos proveedores.

---

## 1. Plugin de AWS en Cursor Marketplace

En el marketplace de Cursor el plugin relevante es **Agent Plugins for AWS**, en particular **deploy-on-aws**. Está pensado para desplegar aplicaciones **en AWS** (Amazon Web Services), no en otras plataformas como Laravel Forge.

### Capacidades principales

| Función | Descripción |
|--------|-------------|
| **Analizar** | Escanea el repositorio: framework, base de datos, dependencias y estructura |
| **Recomendar** | Sugiere servicios AWS (App Runner, S3, RDS, Lambda, CloudFront, etc.) según el proyecto |
| **Estimar** | Muestra coste mensual aproximado usando precios actuales de AWS |
| **Generar** | Produce CDK o CloudFormation para definir la infraestructura |
| **Desplegar** | Ejecuta el despliegue en AWS tras confirmación del usuario |

### Flujo de trabajo

1. **Analizar** – El agente revisa el código
2. **Recomendar** – Propone arquitectura y servicios AWS
3. **Estimar** – Muestra coste mensual antes de desplegar
4. **Generar** – Crea IaC (CDK/CloudFormation)
5. **Desplegar** – Despliega los recursos en AWS

### Servidores MCP usados por el plugin

| MCP Server | Función |
|------------|---------|
| AWS Knowledge | Documentación, arquitectura y buenas prácticas |
| AWS Pricing | Precios en tiempo real para las estimaciones |
| AWS IaC | Buenas prácticas para CDK y CloudFormation |

### Ejemplo de triggers (natural language)

- "Deploy to AWS"
- "Generate infrastructure"
- "Estimate AWS cost"
- "AWS architecture for this app"
- "Run this on AWS"
- "Host on AWS"

### Requisitos

- AWS CLI configurado con credenciales adecuadas
- Editor compatible (Claude Code, Cursor u otro que soporte agent plugins)

---

## 2. Objetivo: Backend en Forge + Frontend en otro proveedor

### Alcance del plugin AWS

El plugin **no integra directamente con Laravel Forge**. Sirve para desplegar en servicios AWS (App Runner, S3, Lambda, CloudFront, etc.), no con la API de Forge.

### Laravel Forge y automatización

Forge gestiona servidores (DigitalOcean, Linode, AWS EC2, etc.) y despliegues. Para automatizar el deploy con Forge se puede usar:

- **Forge API** – Endpoint `POST /api/v1/servers/{id}/sites/{id}/deploy` para lanzar un deploy
- **GitHub Actions** – Pipeline que ejecuta tests y luego llama a la API de Forge para desplegar el backend
- **Forge CLI** – Comando `forge deploy` desde terminal o scripts de CI/CD

### Frontend en otro proveedor

Para frontends desplegados fuera de Forge (Vercel, Netlify, GitHub Pages, etc.) se suele usar:

- **GitHub Actions** (u otro CI) – Build + deploy a la plataforma elegida
- **Integraciones nativas** – Push a rama → deploy automático en Vercel/Netlify

---

## 3. Tabla comparativa

| Objetivo | ¿Sirve el plugin AWS? | Alternativa recomendada |
|----------|------------------------|--------------------------|
| Backend en **Laravel Forge** | No | GitHub Actions + Forge API / Forge CLI |
| Frontend en Vercel, Netlify, etc. | No | GitHub Actions o integración del proveedor |
| Todo (backend + frontend) **en AWS** | Sí | Plugin deploy-on-aws |

---

## 4. Recomendaciones

### Si el backend va a Laravel Forge

1. Configurar GitHub Actions u otro CI para:
   - Ejecutar tests
   - Llamar a la Forge API para desplegar
   - O usar `forge deploy` si se dispone del CLI
2. Mantener los secretos (token de Forge, etc.) en el CI

### Si el frontend va a otro proveedor (Vercel, Netlify)

1. Usar la integración de Git del proveedor
2. O un workflow de GitHub Actions que haga build y deploy

### Si se elige AWS para todo

1. Instalar el plugin deploy-on-aws desde el Cursor Marketplace
2. Tener AWS CLI configurado con credenciales válidas
3. Usar frases como "Deploy this app to AWS" para guiar al agente

---

## 5. Referencias

- [Agent Plugins for AWS (GitHub)](https://github.com/awslabs/agent-plugins)
- [Introducing Agent Plugins for AWS – AWS Developer Tools Blog](https://aws.amazon.com/blogs/developer/introducing-agent-plugins-for-aws/)
- [Cursor Marketplace](https://cursor.com/marketplace)
- [Laravel Forge API / CLI](https://forge.laravel.com/docs)
