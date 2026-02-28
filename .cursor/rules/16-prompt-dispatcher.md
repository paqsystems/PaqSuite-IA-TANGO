# Regla: Dispatcher de Prompts (HU → TR → Ejecución)

## Objetivo
Permitir el uso de comandos abreviados para:
1) Generar TRs a partir de Historias de Usuario
2) Ejecutar una TR existente como fuente de verdad

El objetivo es evitar copy/paste manual y garantizar
un flujo determinístico y reproducible.

---

## PARTE A – Generación de TR desde HU

### Comando: “Aplicá el prompt correspondiente a la historia”

Cuando el usuario escriba:
> Aplicá el prompt correspondiente a la historia HU-xxx.md

El asistente debe:

1. Leer el archivo de la HU indicada.
2. Evaluar la HU usando la regla:
   `16-hu-simple-vs-hu-compleja.md`.
3. Determinar si la HU es **HU Simple** o **HU Compleja**.
4. En función del resultado:
   - Si es HU Simple:
     - Usar la sección **HU Simple** del archivo
       `docs/prompts/04-Prompts-HU-a-Tareas.md`.
   - Si es HU Compleja:
     - Ejecutar el flujo **HU Compleja** (Paso 1 y Paso 2)
       definido en el mismo archivo.
5. Reemplazar el placeholder `[HU]` por el contenido completo
   del archivo de la HU.
6. Ejecutar el prompt resultante como si hubiera sido
   provisto explícitamente por el usuario.
7. Generar el archivo TR según las reglas del prompt.
8. Si existe ambigüedad en la clasificación,
   solicitar confirmación explícita antes de continuar.


---

## PARTE B – Ejecución de una TR

### Comando: “Ejecutá la TR”
Cuando el usuario escriba:
> Ejecutá la TR TR-xxx.md

El asistente debe:
1. Leer el archivo:
   `docs/tareas/TR-xxx.md`
2. Leer el archivo de prompt:
   `docs/prompts/05-Ejecucion-de-una-TR.md`
3. Reemplazar el placeholder `[NOMBRE_DEL_TR]`
   por el nombre del archivo TR indicado
4. Ejecutar el prompt resultante como si hubiera sido
   pegado explícitamente por el usuario
5. Tratar la TR como **FUENTE DE VERDAD del alcance**

---

## PARTE C – Inicialización del entorno de desarrollo

### Comando: “Iniciá el entorno de desarrollo”

Cuando el usuario escriba:
> Iniciá el entorno de desarrollo

El asistente debe:

1. Asumir que el proyecto sigue la siguiente estructura:
   - `backend/` (Laravel)
   - `frontend/` (Vite / React)

2. Indicar la ejecución de los siguientes comandos,
   **en terminales separadas**:

1) Si el backend es mysql, abrir el túnel SSH
En una terminal (mantener abierta):
```powershell
cd "C:\Programacion\PaqSuite-IA-ERP"
.\scripts\ssh-tunnel-mysql.ps1
```
O directamente:
```bash
ssh -i "C:\Users\PabloQ\pablo-notebook" -o StrictHostKeyChecking=no -L 3306:127.0.0.1:3306 -N forge@18.218.140.170
```

2) Backend
en nueva terminal
```bash
cd backend
php artisan serve
```
3) Frontend
en otra terminal
```bash 
cd frontend
npm run dev
```
--- 
## PARTE D – Ejecución de tests (backend + unitarios + E2E)

### Comando: "Ejecutá los tests" / "Corré los tests"

Cuando el usuario escriba algo como:
> Ejecutá los tests
> Corré los tests
> Ejecutá tests unitarios y E2E

el asistente debe:

1. Asumir que el proyecto tiene:
   - `Backend` en backend/ (Laravel, PHPUnit).
   - `Frontend` en frontend/ (Vitest para unitarios, Playwright para E2E).

2. Indicar la ejecución de los siguientes comandos,
   **en terminales separadas** , en este orden:

1) Tests backend
En una terminal:
cd backend
php artisan test
2) Tests unitarios (frontend)
En otra terminal:
cd frontend
npm run test:run
3) Tests E2E (frontend)
En otra terminal:
cd frontend
npm run test:e2e
--- 
--- 
## PARTE E – Hacer commit + push - Texto para PR

### Comando: "Commiteá" ó " Commitea"

Cuando el usuario escriba algo como:
> Commiteá
> actualiza el GitHub

el asistente debe:
1. Hacer commit
2. Hacer push
3. Generar el texto para el PR en el archivo PR-prompt.md, borrado el contenido anterior

---

## Reglas generales (aplican a todos los comandos)
- No inventar prompts fuera de los definidos.
- No modificar HU ni TR sin dejar trazabilidad.
- El reemplazo de placeholders debe ser textual y completo.
- Si el archivo indicado no existe o no es accesible,
  solicitar aclaración antes de continuar.
- Respetar todas las reglas del proyecto y de `.cursor/rules`.

---

## Beneficio
Esta regla habilita un flujo completo y consistente:
- HU → TR (planificación)
- TR → Código, tests y documentación (ejecución)
- Ejecución de tests: backend, unitarios y E2E en terminales separadas con una frase (PARTE D).

Permite usar comandos cortos, claros y sin copy/paste,
reduciendo errores humanos y mejorando la productividad.

### Invocaciones combinadas

Si el usuario escribe en un mismo mensaje ambas frases (por ejemplo: **Iniciá el entorno de desarrollo** y **ejecutá los tests**), el asistente debe aplicar primero la PARTE C (abrir terminales con backend y frontend en ejecución) y luego la PARTE D (abrir las tres terminales de tests: backend, unitarios, E2E). Las invocaciones son acumulables en el orden C → D.
