# Documentación: listarIssuesJira.py

## Descripción

Script en Python para listar todos los issues asignados al usuario actual en Jira. El script se conecta a la API de Jira y muestra información detallada de cada issue asignado.

## Requisitos

- Python 3.7 o superior
- Cuenta de Jira con acceso a la API
- Token de API de Jira

## Instalación

1. Instalar las dependencias necesarias:

```bash
pip install -r requirements.txt
```

## Configuración

1. Crear un archivo `.env` en la raíz del proyecto con la siguiente estructura:

```env
# URL de tu servidor de Jira
JIRA_URL=https://tu-empresa.atlassian.net

# Email de tu cuenta de Jira
JIRA_EMAIL=tu-email@ejemplo.com

# Token de API de Jira
JIRA_API_TOKEN=tu-token-de-api-aqui
```

### Cómo obtener el Token de API de Jira

1. Accede a tu perfil de Atlassian: https://id.atlassian.com/manage-profile/security/api-tokens
2. Haz clic en "Create API token"
3. Asigna un nombre descriptivo al token
4. Copia el token generado (solo se muestra una vez)
5. Pega el token en el archivo `.env`

## Uso

Ejecutar el script desde la línea de comandos:

```bash
python listarIssuesJira.py
```

## Funcionalidad

El script realiza las siguientes acciones:

1. **Carga la configuración**: Lee las variables de entorno del archivo `.env`
2. **Valida la configuración**: Verifica que todas las variables necesarias estén presentes
3. **Conecta con Jira**: Establece conexión con la API de Jira usando autenticación básica
4. **Obtiene issues**: Busca todos los issues asignados al usuario actual que estén sin resolver
5. **Muestra resultados**: Presenta la información de cada issue de forma estructurada

## Información mostrada

Para cada issue, el script muestra:

- **Key**: Identificador único del issue (ej: PROJ-123)
- **Tipo**: Tipo de issue (Bug, Story, Task, etc.)
- **Estado**: Estado actual del issue
- **Prioridad**: Prioridad asignada
- **Proyecto**: Proyecto al que pertenece
- **Fecha de creación**: Fecha en que se creó el issue
- **Fecha de actualización**: Última fecha de modificación
- **Resumen**: Descripción breve del issue
- **URL**: Enlace directo al issue en Jira

## Filtros aplicados

El script utiliza la siguiente consulta JQL (Jira Query Language):

```
assignee = currentUser() AND resolution = Unresolved ORDER BY updated DESC
```

Esto significa que solo se muestran:
- Issues asignados al usuario actual
- Issues sin resolver (abiertos)
- Ordenados por fecha de actualización (más recientes primero)

## Clase JiraClient

### Métodos principales

#### `__init__(jiraUrl, email, apiToken)`
Inicializa el cliente de Jira con las credenciales necesarias.

#### `obtenerIssuesAsignados(maxResults=100)`
Obtiene la lista de issues asignados al usuario.

**Parámetros:**
- `maxResults` (int): Número máximo de resultados a obtener (por defecto 100)

**Retorna:**
- Lista de diccionarios con la información de los issues

#### `formatearIssue(issue)`
Formatea un issue para mostrarlo de manera legible.

**Parámetros:**
- `issue` (dict): Diccionario con la información del issue

**Retorna:**
- String formateado con la información del issue

## Manejo de errores

El script incluye manejo de errores para:

- Variables de entorno faltantes
- Errores de conexión con Jira
- Errores de autenticación
- Errores de la API de Jira

En caso de error, se mostrará un mensaje descriptivo y el script terminará con código de salida 1.

## Personalización

### Cambiar el número máximo de resultados

Modifica el parámetro `maxResults` en la llamada a `obtenerIssuesAsignados()`:

```python
issues = cliente.obtenerIssuesAsignados(maxResults=200)
```

### Modificar la consulta JQL

Puedes cambiar la consulta JQL en el método `obtenerIssuesAsignados()` para filtrar de manera diferente:

```python
jql = "assignee = currentUser() AND status = 'In Progress' ORDER BY priority DESC"
```

### Agregar más campos

Para obtener más información de cada issue, modifica el parámetro `fields` en el payload (debe ser un array):

```python
payload = {
    "jql": jql,
    "maxResults": maxResults,
    "fields": ["summary", "status", "priority", "issuetype", "created", "updated", "project", "description", "reporter"]
}
```

## Dependencias

- **requests**: Para realizar peticiones HTTP a la API de Jira
- **python-dotenv**: Para cargar variables de entorno desde el archivo `.env`

## Notas

- El token de API es más seguro que usar contraseñas directamente
- El archivo `.env` no debe subirse al repositorio (debe estar en `.gitignore`)
- El script usa la API v3 de Jira con el endpoint `/rest/api/3/search/jql` (endpoint actualizado según las últimas especificaciones de Atlassian)
- El nuevo endpoint requiere enviar el JQL en el cuerpo de la petición POST en lugar de parámetros GET

