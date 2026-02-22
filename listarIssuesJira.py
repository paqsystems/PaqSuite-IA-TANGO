"""
Script para listar los issues asignados al usuario en Jira.

Este script se conecta a la API de Jira y obtiene todos los issues
asignados al usuario autenticado.
"""

import os
import sys
import requests
from requests.auth import HTTPBasicAuth
from dotenv import load_dotenv
from typing import List, Dict, Optional

# Cargar variables de entorno
load_dotenv()


class JiraClient:
    """Cliente para interactuar con la API de Jira."""

    def __init__(self, jiraUrl: str, email: str, apiToken: str):
        """
        Inicializa el cliente de Jira.

        Args:
            jiraUrl: URL base del servidor de Jira (ej: https://tu-empresa.atlassian.net)
            email: Email del usuario de Jira
            apiToken: Token de API de Jira
        """
        self.jiraUrl = jiraUrl.rstrip('/')
        self.email = email
        self.apiToken = apiToken
        self.auth = HTTPBasicAuth(email, apiToken)
        self.headers = {
            "Accept": "application/json",
            "Content-Type": "application/json"
        }

    def obtenerIssuesAsignados(self, maxResults: int = 100) -> List[Dict]:
        """
        Obtiene todos los issues asignados al usuario actual.

        Args:
            maxResults: Número máximo de resultados a obtener (por defecto 100)

        Returns:
            Lista de diccionarios con la información de los issues
        """
        # Usar el nuevo endpoint /rest/api/3/search/jql
        url = f"{self.jiraUrl}/rest/api/3/search/jql"
        
        # JQL query para obtener issues asignados al usuario actual
        jql = "assignee = currentUser() AND resolution = Unresolved ORDER BY updated DESC"
        
        # El nuevo endpoint requiere enviar los datos en el cuerpo de la petición
        payload = {
            "jql": jql,
            "maxResults": maxResults,
            "fields": ["summary", "status", "priority", "issuetype", "created", "updated", "project"]
        }

        try:
            response = requests.post(
                url,
                headers=self.headers,
                auth=self.auth,
                json=payload
            )
            response.raise_for_status()
            
            data = response.json()
            return data.get("issues", [])
            
        except requests.exceptions.RequestException as e:
            print(f"Error al conectar con Jira: {e}")
            if hasattr(e, 'response') and e.response is not None:
                print(f"Respuesta del servidor: {e.response.text}")
            sys.exit(1)

    def formatearIssue(self, issue: Dict) -> str:
        """
        Formatea un issue para mostrarlo de manera legible.

        Args:
            issue: Diccionario con la información del issue

        Returns:
            String formateado con la información del issue
        """
        key = issue.get("key", "N/A")
        fields = issue.get("fields", {})
        
        summary = fields.get("summary", "Sin resumen")
        status = fields.get("status", {}).get("name", "N/A")
        priority = fields.get("priority", {}).get("name", "N/A")
        issueType = fields.get("issuetype", {}).get("name", "N/A")
        project = fields.get("project", {}).get("name", "N/A")
        created = fields.get("created", "N/A")
        updated = fields.get("updated", "N/A")
        
        # Formatear fechas si están disponibles
        if created != "N/A":
            created = created[:10]  # Solo fecha, sin hora
        if updated != "N/A":
            updated = updated[:10]
        
        issueUrl = f"{self.jiraUrl}/browse/{key}"
        
        return f"""
┌─────────────────────────────────────────────────────────────┐
│ {key:60} │
├─────────────────────────────────────────────────────────────┤
│ Tipo:        {issueType:45} │
│ Estado:      {status:45} │
│ Prioridad:   {priority:45} │
│ Proyecto:    {project:45} │
│ Creado:      {created:45} │
│ Actualizado: {updated:45} │
├─────────────────────────────────────────────────────────────┤
│ {summary:61} │
│                                                             │
│ URL: {issueUrl:57} │
└─────────────────────────────────────────────────────────────┘
"""


def main():
    """Función principal del script."""
    # Obtener configuración de variables de entorno
    jiraUrl = os.getenv("JIRA_URL")
    email = os.getenv("JIRA_EMAIL")
    apiToken = os.getenv("JIRA_API_TOKEN")

    # Validar que todas las variables estén configuradas
    if not jiraUrl:
        print("Error: JIRA_URL no está configurada en el archivo .env")
        sys.exit(1)
    if not email:
        print("Error: JIRA_EMAIL no está configurada en el archivo .env")
        sys.exit(1)
    if not apiToken:
        print("Error: JIRA_API_TOKEN no está configurada en el archivo .env")
        sys.exit(1)

    # Crear cliente de Jira
    cliente = JiraClient(jiraUrl, email, apiToken)

    print("Conectando con Jira...")
    print(f"URL: {jiraUrl}")
    print(f"Usuario: {email}\n")

    # Obtener issues asignados
    issues = cliente.obtenerIssuesAsignados()

    if not issues:
        print("No se encontraron issues asignados.")
        return

    print(f"\n{'='*65}")
    print(f"Total de issues asignados: {len(issues)}")
    print(f"{'='*65}\n")

    # Mostrar cada issue
    for issue in issues:
        print(cliente.formatearIssue(issue))
        print()


if __name__ == "__main__":
    main()

