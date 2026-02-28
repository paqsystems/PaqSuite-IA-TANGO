Vamos a hacer toda una definición con respecto a lo que llamo "Parámetros Generales del Sistema"

# Objetivo

Tener por módulo, una serie de valores que sean configurables por el usuario, con el fin de que el sistema sea adaptable a distinta alternativas o versiones solicitadas por el usuario, o datos que son necesarios configurar una sola vez, y no queremos "harcodearlo" (amén que por cada instalación ese valor varía).

# Cómo aplicarlo.

1) tener una tabla por cada empresa donde se guardarán estos valores (en tópico más abajo aclararemos el diseño). El archivo lo llamaremos PQ_PARAMETROS_GRAL.
2) cuando se presente el contexto y objetivo de un módulo y se pida confeccionar todas las HU del mismo, habrá que definir una palabra clave (nombre del modulo sin espacios), que se almacenará en el campo "Programa" y todos los datos que se definan hay que incluir en esa tabla de parámetros. por cada dato hay que aclarar el tipo (varchar, text, datetime, integer, decimal, etc) el cual se almacenará en el campo TIPO_VALOR. habrá que definir también un nombre clave para cada dato (que se almacenarán en el campo CLAVE)
3) habrá una historia específica para generar el proceso de poder editar dicha tabla, limitada a los registros que corresponden a este módulo.
4) durante las definiciones de las otras historias se hará mención cuando se quiere utilizar alguno de estos parámetros, mencionando su nombre clave. se dará por sobreentendido el nombre del campo PROGRAMA.
5) Habrá que incluir para los deploys en los clientes o en desarrollo, la subida de estos registros de esta tabla, al igual que la tabla "PQ_MENUS"
6) La palabra clave que se almacena en PROGRAMA, se cargará en el campo PQ_MENUS.PROCEDIMIENTO de cada registro que invoque al proceso de mantenimiento de esta tabla, para filtrar los registros a procesar.
7) El proceso de tratamiento de esta tabla : no permite eliminar ni agregar registros, sólo permite editar el campo correspondiente llamado "valor_....." según el atributo TIPO_VALOR.

# Diseño del archivo

CREATE TABLE [dbo].[PQ_PARAMETROS_GRAL](
	[Programa] [varchar](50) NOT NULL,
	[Clave] [varchar](50) NOT NULL,
	[tipo_valor] [char](1) NULL,
	[Valor_String] [varchar](255) NULL,
	[Valor_Text] [text] NULL,
	[Valor_Int] [int] NULL,
	[Valor_DateTime] [datetime] NULL,
	[Valor_Bool] [bit] NULL,
	[Valor_Decimal] [numeric](24, 6) NULL,
 CONSTRAINT [PK_PQ_PARAMETROS_GRAL] PRIMARY KEY CLUSTERED 
(
	[Programa] ASC,
	[Clave] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

# Qué se debe confeccionar.

1) ~~Documentar donde corresponda el diseño de este archivo con su respectivo erDiagram~~ ✅ `docs/modelo-datos/md-empresas/pq-parametros-gral.md` (tabla en Company DB, no en diccionario)
2) ~~Documentar la regla para aclarar el armado (y formato) de la HU de parametros generales cada vez que se generen los HU de un módulo~~ ✅ `.cursor/rules/27-parametros-generales-por-modulo.md`
3) ~~Documentar la regla de cómo hacer el plan de tareas de este HU específico, donde se confecciona el proceso una sola vez. en este HU aclarar el nombre a usar como clave del módulo (campo PROGRAMA)~~ ✅ `.cursor/rules/28-plan-tareas-hu-parametros-generales.md`
4) ~~Generar la HU docs/03-hu-historias/000-Generalidades/HU-007-Parametros-generales.md con la historia para desarrollar el proceso general de tratamiento de esta tabla (las HU del punto 2 sólo debe invocar a este proceso, aclarando el nombre clave y el de todas las claves a utilizar - aclarando su respectivo tipo de dato)~~ ✅ `docs/03-hu-historias/000-Generalidades/HU-007-Parametros-generales.md`

