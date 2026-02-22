#!/usr/bin/env node

/**
 * MCP Server para visualización de modelo de datos
 * Lee el archivo .dbml y genera diagramas Mermaid
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  ListResourcesRequestSchema,
  ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import { readFileSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const PROJECT_ROOT = join(__dirname, '../..');

// Ruta al archivo DBML
const DBML_FILE = join(PROJECT_ROOT, 'database', 'modelo-datos.dbml');
const MARKDOWN_FILE = join(PROJECT_ROOT, 'docs', 'modelo-datos.md');

class DatabaseDiagramServer {
  constructor() {
    this.server = new Server(
      {
        name: 'db-diagram',
        version: '1.0.0',
      },
      {
        capabilities: {
          tools: {},
          resources: {},
        },
      }
    );

    this.setupHandlers();
  }

  setupHandlers() {
    // Listar herramientas disponibles
    this.server.setRequestHandler(ListToolsRequestSchema, async () => ({
      tools: [
        {
          name: 'visualizar_modelo',
          description: 'Genera un diagrama Mermaid del modelo de datos desde el archivo DBML',
          inputSchema: {
            type: 'object',
            properties: {
              formato: {
                type: 'string',
                enum: ['mermaid', 'texto'],
                description: 'Formato de salida: mermaid (diagrama) o texto (descripción)',
                default: 'mermaid',
              },
            },
          },
        },
        {
          name: 'leer_dbml',
          description: 'Lee el contenido del archivo DBML del modelo de datos',
          inputSchema: {
            type: 'object',
            properties: {},
          },
        },
        {
          name: 'verificar_sincronizacion',
          description: 'Verifica si el archivo DBML está sincronizado con el modelo de datos en markdown',
          inputSchema: {
            type: 'object',
            properties: {},
          },
        },
      ],
    }));

    // Listar recursos disponibles
    this.server.setRequestHandler(ListResourcesRequestSchema, async () => ({
      resources: [
        {
          uri: 'dbml://modelo-datos',
          name: 'Modelo de Datos DBML',
          description: 'Archivo DBML del modelo de datos',
          mimeType: 'text/plain',
        },
        {
          uri: 'mermaid://modelo-diagrama',
          name: 'Diagrama Mermaid del Modelo',
          description: 'Diagrama ER en formato Mermaid',
          mimeType: 'text/markdown',
        },
      ],
    }));

    // Leer recursos
    this.server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
      const { uri } = request.params;

      if (uri === 'dbml://modelo-datos') {
        if (!existsSync(DBML_FILE)) {
          throw new Error(`Archivo DBML no encontrado: ${DBML_FILE}`);
        }
        const content = readFileSync(DBML_FILE, 'utf-8');
        return {
          contents: [
            {
              uri,
              mimeType: 'text/plain',
              text: content,
            },
          ],
        };
      }

      if (uri === 'mermaid://modelo-diagrama') {
        const mermaid = this.generateMermaidFromDBML();
        return {
          contents: [
            {
              uri,
              mimeType: 'text/markdown',
              text: `\`\`\`mermaid\n${mermaid}\n\`\`\``,
            },
          ],
        };
      }

      throw new Error(`Recurso no encontrado: ${uri}`);
    });

    // Ejecutar herramientas
    this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
      const { name, arguments: args } = request.params;

      try {
        switch (name) {
          case 'visualizar_modelo':
            return await this.handleVisualizarModelo(args?.formato || 'mermaid');

          case 'leer_dbml':
            return await this.handleLeerDBML();

          case 'verificar_sincronizacion':
            return await this.handleVerificarSincronizacion();

          default:
            throw new Error(`Herramienta desconocida: ${name}`);
        }
      } catch (error) {
        return {
          content: [
            {
              type: 'text',
              text: `Error: ${error.message}`,
            },
          ],
          isError: true,
        };
      }
    });
  }

  handleVisualizarModelo(formato) {
    if (formato === 'mermaid') {
      const mermaid = this.generateMermaidFromDBML();
      return {
        content: [
          {
            type: 'text',
            text: `Diagrama Mermaid generado:\n\n\`\`\`mermaid\n${mermaid}\n\`\`\`\n\n**Para visualizar en Cursor:**\n1. Abre la vista previa de Markdown (Ctrl+Shift+V / Cmd+Shift+V)\n2. O instala la extensión "Markdown Preview Mermaid Support"`,
          },
        ],
      };
    } else {
      // Formato texto
      if (!existsSync(DBML_FILE)) {
        throw new Error(`Archivo DBML no encontrado: ${DBML_FILE}`);
      }
      const content = readFileSync(DBML_FILE, 'utf-8');
      return {
        content: [
          {
            type: 'text',
            text: `Contenido del archivo DBML:\n\n\`\`\`\n${content}\n\`\`\``,
          },
        ],
      };
    }
  }

  handleLeerDBML() {
    if (!existsSync(DBML_FILE)) {
      throw new Error(`Archivo DBML no encontrado: ${DBML_FILE}`);
    }
    const content = readFileSync(DBML_FILE, 'utf-8');
    return {
      content: [
        {
          type: 'text',
          text: content,
        },
      ],
    };
  }

  handleVerificarSincronizacion() {
    const dbmlExists = existsSync(DBML_FILE);
    const markdownExists = existsSync(MARKDOWN_FILE);

    let message = '**Estado de sincronización:**\n\n';
    message += `- Archivo DBML: ${dbmlExists ? '✅ Existe' : '❌ No existe'}\n`;
    message += `- Archivo Markdown: ${markdownExists ? '✅ Existe' : '❌ No existe'}\n\n`;

    if (dbmlExists && markdownExists) {
      const dbmlContent = readFileSync(DBML_FILE, 'utf-8');
      const markdownContent = readFileSync(MARKDOWN_FILE, 'utf-8');

      // Verificación básica: contar tablas
      const dbmlTables = (dbmlContent.match(/Table\s+\w+/g) || []).length;
      const markdownTables = (markdownContent.match(/###\s+\w+/g) || []).length;

      message += `- Tablas en DBML: ${dbmlTables}\n`;
      message += `- Entidades en Markdown: ${markdownTables}\n\n`;

      if (Math.abs(dbmlTables - markdownTables) <= 1) {
        message += '✅ Los archivos parecen estar sincronizados.';
      } else {
        message += '⚠️ Puede haber desincronización. Revisa manualmente.';
      }
    }

    return {
      content: [
        {
          type: 'text',
          text: message,
        },
      ],
    };
  }

  generateMermaidFromDBML() {
    if (!existsSync(DBML_FILE)) {
      throw new Error(`Archivo DBML no encontrado: ${DBML_FILE}`);
    }

    const dbmlContent = readFileSync(DBML_FILE, 'utf-8');
    
    // Parsear DBML básico y generar Mermaid
    // Esta es una implementación simplificada
    const tables = [];
    const relationships = [];
    
    // Extraer tablas
    const tableMatches = dbmlContent.matchAll(/Table\s+(\w+)\s*\{([^}]+)\}/g);
    for (const match of tableMatches) {
      const tableName = match[1];
      const tableBody = match[2];
      
      // Extraer campos básicos
      const fields = [];
      const fieldMatches = tableBody.matchAll(/(\w+)\s+(\w+)(?:\s*\[([^\]]+)\])?/g);
      for (const fieldMatch of fieldMatches) {
        const fieldName = fieldMatch[1];
        const fieldType = fieldMatch[2];
        const constraints = fieldMatch[3] || '';
        
        let fieldDesc = `${fieldType} ${fieldName}`;
        if (constraints.includes('pk')) fieldDesc += ' PK';
        if (constraints.includes('unique')) fieldDesc += ' UK';
        if (constraints.includes('ref:')) {
          const refMatch = constraints.match(/ref:\s*[><]\s*(\w+)\.(\w+)/);
          if (refMatch) {
            relationships.push({
              from: tableName,
              to: refMatch[1],
              fromField: fieldName,
              toField: refMatch[2],
            });
          }
        }
        
        fields.push(fieldDesc);
      }
      
      tables.push({ name: tableName, fields });
    }
    
    // Generar diagrama Mermaid
    let mermaid = 'erDiagram\n';
    
    // Agregar relaciones
    for (const rel of relationships) {
      mermaid += `    ${rel.from} ||--o{ ${rel.to} : "${rel.fromField}"\n`;
    }
    
    // Agregar definiciones de tablas
    mermaid += '\n';
    for (const table of tables) {
      mermaid += `    ${table.name} {\n`;
      for (const field of table.fields.slice(0, 8)) { // Limitar campos para legibilidad
        mermaid += `        ${field}\n`;
      }
      if (table.fields.length > 8) {
        mermaid += `        ... ${table.fields.length - 8} campos más\n`;
      }
      mermaid += `    }\n`;
    }
    
    return mermaid;
  }

  async run() {
    const transport = new StdioServerTransport();
    await this.server.connect(transport);
    console.error('MCP Server DB Diagram iniciado');
  }
}

const server = new DatabaseDiagramServer();
server.run().catch(console.error);
