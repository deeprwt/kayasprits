﻿/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Spanish language file.
 */

var FCKLang =
{
// Language direction : "ltr" (left to right) or "rtl" (right to left).
Dir					: "ltr",

ToolbarCollapse		: "Contraer Barra",
ToolbarExpand		: "Expandir Barra",

// Toolbar Items and Context Menu
Save				: "Guardar",
NewPage				: "Nueva Página",
Preview				: "Vista Previa",
Cut					: "Cortar",
Copy				: "Copiar",
Paste				: "Pegar",
PasteText			: "Pegar como texto plano",
PasteWord			: "Pegar desde Word",
Print				: "Imprimir",
SelectAll			: "Seleccionar Todo",
RemoveFormat		: "Eliminar Formato",
InsertLinkLbl		: "Vínculo",
InsertLink			: "Insertar/Editar Vínculo",
RemoveLink			: "Eliminar Vínculo",
Anchor				: "Referencia",
AnchorDelete		: "Remove Anchor",	//MISSING
InsertImageLbl		: "Imagen",
InsertImage			: "Insertar/Editar Imagen",
InsertFlashLbl		: "Flash",
InsertFlash			: "Insertar/Editar Flash",
InsertTableLbl		: "Tabla",
InsertTable			: "Insertar/Editar Tabla",
InsertLineLbl		: "Línea",
InsertLine			: "Insertar Línea Horizontal",
InsertSpecialCharLbl: "Caracter Especial",
InsertSpecialChar	: "Insertar Caracter Especial",
InsertSmileyLbl		: "Emoticons",
InsertSmiley		: "Insertar Emoticons",
About				: "Acerca de FCKeditor",
Bold				: "Negrita",
Italic				: "Cursiva",
Underline			: "Subrayado",
StrikeThrough		: "Tachado",
Subscript			: "Subíndice",
Superscript			: "Superíndice",
LeftJustify			: "Alinear a Izquierda",
CenterJustify		: "Centrar",
RightJustify		: "Alinear a Derecha",
BlockJustify		: "Justificado",
DecreaseIndent		: "Disminuir Sangría",
IncreaseIndent		: "Aumentar Sangría",
Blockquote			: "Blockquote",	//MISSING
Undo				: "Deshacer",
Redo				: "Rehacer",
NumberedListLbl		: "Numeración",
NumberedList		: "Insertar/Eliminar Numeración",
BulletedListLbl		: "Viñetas",
BulletedList		: "Insertar/Eliminar Viñetas",
ShowTableBorders	: "Mostrar Bordes de Tablas",
ShowDetails			: "Mostrar saltos de Párrafo",
Style				: "Estilo",
FontFormat			: "Formato",
Font				: "Fuente",
FontSize			: "Tamaño",
TextColor			: "Color de Texto",
BGColor				: "Color de Fondo",
Source				: "Fuente HTML",
Find				: "Buscar",
Replace				: "Reemplazar",
SpellCheck			: "Ortografía",
UniversalKeyboard	: "Teclado Universal",
PageBreakLbl		: "Salto de Página",
PageBreak			: "Insertar Salto de Página",

Form			: "Formulario",
Checkbox		: "Casilla de Verificación",
RadioButton		: "Botones de Radio",
TextField		: "Campo de Texto",
Textarea		: "Area de Texto",
HiddenField		: "Campo Oculto",
Button			: "Botón",
SelectionField	: "Campo de Selección",
ImageButton		: "Botón Imagen",

FitWindow		: "Maximizar el tamaño del editor",
ShowBlocks		: "Show Blocks",	//MISSING

// Context Menu
EditLink			: "Editar Vínculo",
CellCM				: "Celda",
RowCM				: "Fila",
ColumnCM			: "Columna",
InsertRowAfter		: "Insert Row After",	//MISSING
InsertRowBefore		: "Insert Row Before",	//MISSING
DeleteRows			: "Eliminar Filas",
InsertColumnAfter	: "Insert Column After",	//MISSING
InsertColumnBefore	: "Insert Column Before",	//MISSING
DeleteColumns		: "Eliminar Columnas",
InsertCellAfter		: "Insert Cell After",	//MISSING
InsertCellBefore	: "Insert Cell Before",	//MISSING
DeleteCells			: "Eliminar Celdas",
MergeCells			: "Combinar Celdas",
MergeRight			: "Merge Right",	//MISSING
MergeDown			: "Merge Down",	//MISSING
HorizontalSplitCell	: "Split Cell Horizontally",	//MISSING
VerticalSplitCell	: "Split Cell Vertically",	//MISSING
TableDelete			: "Eliminar Tabla",
CellProperties		: "Propiedades de Celda",
TableProperties		: "Propiedades de Tabla",
ImageProperties		: "Propiedades de Imagen",
FlashProperties		: "Propiedades de Flash",

AnchorProp			: "Propiedades de Referencia",
ButtonProp			: "Propiedades de Botón",
CheckboxProp		: "Propiedades de Casilla",
HiddenFieldProp		: "Propiedades de Campo Oculto",
RadioButtonProp		: "Propiedades de Botón de Radio",
ImageButtonProp		: "Propiedades de Botón de Imagen",
TextFieldProp		: "Propiedades de Campo de Texto",
SelectionFieldProp	: "Propiedades de Campo de Selección",
TextareaProp		: "Propiedades de Area de Texto",
FormProp			: "Propiedades de Formulario",

FontFormats			: "Normal;Con formato;Dirección;Encabezado 1;Encabezado 2;Encabezado 3;Encabezado 4;Encabezado 5;Encabezado 6;Normal (DIV)",

// Alerts and Messages
ProcessingXHTML		: "Procesando XHTML. Por favor, espere...",
Done				: "Hecho",
PasteWordConfirm	: "El texto que desea parece provenir de Word. Desea depurarlo antes de pegarlo?",
NotCompatiblePaste	: "Este comando está disponible sólo para Internet Explorer version 5.5 or superior. Desea pegar sin depurar?",
UnknownToolbarItem	: "Item de barra desconocido \"%1\"",
UnknownCommand		: "Nombre de comando desconocido \"%1\"",
NotImplemented		: "Comando no implementado",
UnknownToolbarSet	: "Nombre de barra \"%1\" no definido",
NoActiveX			: "La configuración de las opciones de seguridad de su navegador puede estar limitando algunas características del editor. Por favor active la opción \"Ejecutar controles y complementos de ActiveX \", de lo contrario puede experimentar errores o ausencia de funcionalidades.",
BrowseServerBlocked : "La ventana de visualización del servidor no pudo ser abierta. Verifique que su navegador no esté bloqueando las ventanas emergentes (pop up).",
DialogBlocked		: "No se ha podido abrir la ventana de diálogo. Verifique que su navegador no esté bloqueando las ventanas emergentes (pop up).",

// Dialogs
DlgBtnOK			: "OK",
DlgBtnCancel		: "Cancelar",
DlgBtnClose			: "Cerrar",
DlgBtnBrowseServer	: "Ver Servidor",
DlgAdvancedTag		: "Avanzado",
DlgOpOther			: "<Otro>",
DlgInfoTab			: "Información",
DlgAlertUrl			: "Inserte el URL",

// General Dialogs Labels
DlgGenNotSet		: "<No definido>",
DlgGenId			: "Id",
DlgGenLangDir		: "Orientación de idioma",
DlgGenLangDirLtr	: "Izquierda a Derecha (LTR)",
DlgGenLangDirRtl	: "Derecha a Izquierda (RTL)",
DlgGenLangCode		: "Código de idioma",
DlgGenAccessKey		: "Clave de Acceso",
DlgGenName			: "Nombre",
DlgGenTabIndex		: "Indice de tabulación",
DlgGenLongDescr		: "Descripción larga URL",
DlgGenClass			: "Clases de hojas de estilo",
DlgGenTitle			: "Título",
DlgGenContType		: "Tipo de Contenido",
DlgGenLinkCharset	: "Fuente de caracteres vinculado",
DlgGenStyle			: "Estilo",

// Image Dialog
DlgImgTitle			: "Propiedades de Imagen",
DlgImgInfoTab		: "Información de Imagen",
DlgImgBtnUpload		: "Enviar al Servidor",
DlgImgURL			: "URL",
DlgImgUpload		: "Cargar",
DlgImgAlt			: "Texto Alternativo",
DlgImgWidth			: "Anchura",
DlgImgHeight		: "Altura",
DlgImgLockRatio		: "Proporcional",
DlgBtnResetSize		: "Tamaño Original",
DlgImgBorder		: "Borde",
DlgImgHSpace		: "Esp.Horiz",
DlgImgVSpace		: "Esp.Vert",
DlgImgAlign			: "Alineación",
DlgImgAlignLeft		: "Izquierda",
DlgImgAlignAbsBottom: "Abs inferior",
DlgImgAlignAbsMiddle: "Abs centro",
DlgImgAlignBaseline	: "Línea de base",
DlgImgAlignBottom	: "Pie",
DlgImgAlignMiddle	: "Centro",
DlgImgAlignRight	: "Derecha",
DlgImgAlignTextTop	: "Tope del texto",
DlgImgAlignTop		: "Tope",
DlgImgPreview		: "Vista Previa",
DlgImgAlertUrl		: "Por favor tipee el URL de la imagen",
DlgImgLinkTab		: "Vínculo",

// Flash Dialog
DlgFlashTitle		: "Propiedades de Flash",
DlgFlashChkPlay		: "Autoejecución",
DlgFlashChkLoop		: "Repetir",
DlgFlashChkMenu		: "Activar Menú Flash",
DlgFlashScale		: "Escala",
DlgFlashScaleAll	: "Mostrar todo",
DlgFlashScaleNoBorder	: "Sin Borde",
DlgFlashScaleFit	: "Ajustado",

// Link Dialog
DlgLnkWindowTitle	: "Vínculo",
DlgLnkInfoTab		: "Información de Vínculo",
DlgLnkTargetTab		: "Destino",

DlgLnkType			: "Tipo de vínculo",
DlgLnkTypeURL		: "URL",
DlgLnkTypeAnchor	: "Referencia en esta página",
DlgLnkTypeEMail		: "E-Mail",
DlgLnkProto			: "Protocolo",
DlgLnkProtoOther	: "<otro>",
DlgLnkURL			: "URL",
DlgLnkAnchorSel		: "Seleccionar una referencia",
DlgLnkAnchorByName	: "Por Nombre de Referencia",
DlgLnkAnchorById	: "Por ID de elemento",
DlgLnkNoAnchors		: "(No hay referencias disponibles en el documento)",
DlgLnkEMail			: "Dirección de E-Mail",
DlgLnkEMailSubject	: "Título del Mensaje",
DlgLnkEMailBody		: "Cuerpo del Mensaje",
DlgLnkUpload		: "Cargar",
DlgLnkBtnUpload		: "Enviar al Servidor",

DlgLnkTarget		: "Destino",
DlgLnkTargetFrame	: "<marco>",
DlgLnkTargetPopup	: "<ventana emergente>",
DlgLnkTargetBlank	: "Nueva Ventana(_blank)",
DlgLnkTargetParent	: "Ventana Padre (_parent)",
DlgLnkTargetSelf	: "Misma Ventana (_self)",
DlgLnkTargetTop		: "Ventana primaria (_top)",
DlgLnkTargetFrameName	: "Nombre del Marco Destino",
DlgLnkPopWinName	: "Nombre de Ventana Emergente",
DlgLnkPopWinFeat	: "Características de Ventana Emergente",
DlgLnkPopResize		: "Ajustable",
DlgLnkPopLocation	: "Barra de ubicación",
DlgLnkPopMenu		: "Barra de Menú",
DlgLnkPopScroll		: "Barras de desplazamiento",
DlgLnkPopStatus		: "Barra de Estado",
DlgLnkPopToolbar	: "Barra de Herramientas",
DlgLnkPopFullScrn	: "Pantalla Completa (IE)",
DlgLnkPopDependent	: "Dependiente (Netscape)",
DlgLnkPopWidth		: "Anchura",
DlgLnkPopHeight		: "Altura",
DlgLnkPopLeft		: "Posición Izquierda",
DlgLnkPopTop		: "Posición Derecha",

DlnLnkMsgNoUrl		: "Por favor tipee el vínculo URL",
DlnLnkMsgNoEMail	: "Por favor tipee la dirección de e-mail",
DlnLnkMsgNoAnchor	: "Por favor seleccione una referencia",
DlnLnkMsgInvPopName	: "The popup name must begin with an alphabetic character and must not contain spaces",	//MISSING

// Color Dialog
DlgColorTitle		: "Seleccionar Color",
DlgColorBtnClear	: "Ninguno",
DlgColorHighlight	: "Resaltado",
DlgColorSelected	: "Seleccionado",

// Smiley Dialog
DlgSmileyTitle		: "Insertar un Emoticon",

// Special Character Dialog
DlgSpecialCharTitle	: "Seleccione un caracter especial",

// Table Dialog
DlgTableTitle		: "Propiedades de Tabla",
DlgTableRows		: "Filas",
DlgTableColumns		: "Columnas",
DlgTableBorder		: "Tamaño de Borde",
DlgTableAlign		: "Alineación",
DlgTableAlignNotSet	: "<No establecido>",
DlgTableAlignLeft	: "Izquierda",
DlgTableAlignCenter	: "Centrado",
DlgTableAlignRight	: "Derecha",
DlgTableWidth		: "Anchura",
DlgTableWidthPx		: "pixeles",
DlgTableWidthPc		: "porcentaje",
DlgTableHeight		: "Altura",
DlgTableCellSpace	: "Esp. e/celdas",
DlgTableCellPad		: "Esp. interior",
DlgTableCaption		: "Título",
DlgTableSummary		: "Síntesis",

// Table Cell Dialog
DlgCellTitle		: "Propiedades de Celda",
DlgCellWidth		: "Anchura",
DlgCellWidthPx		: "pixeles",
DlgCellWidthPc		: "porcentaje",
DlgCellHeight		: "Altura",
DlgCellWordWrap		: "Cortar Línea",
DlgCellWordWrapNotSet	: "<No establecido>",
DlgCellWordWrapYes	: "Si",
DlgCellWordWrapNo	: "No",
DlgCellHorAlign		: "Alineación Horizontal",
DlgCellHorAlignNotSet	: "<No establecido>",
DlgCellHorAlignLeft	: "Izquierda",
DlgCellHorAlignCenter	: "Centrado",
DlgCellHorAlignRight: "Derecha",
DlgCellVerAlign		: "Alineación Vertical",
DlgCellVerAlignNotSet	: "<Not establecido>",
DlgCellVerAlignTop	: "Tope",
DlgCellVerAlignMiddle	: "Medio",
DlgCellVerAlignBottom	: "ie",
DlgCellVerAlignBaseline	: "Línea de Base",
DlgCellRowSpan		: "Abarcar Filas",
DlgCellCollSpan		: "Abarcar Columnas",
DlgCellBackColor	: "Color de Fondo",
DlgCellBorderColor	: "Color de Borde",
DlgCellBtnSelect	: "Seleccione...",

// Find and Replace Dialog
DlgFindAndReplaceTitle	: "Find and Replace",	//MISSING

// Find Dialog
DlgFindTitle		: "Buscar",
DlgFindFindBtn		: "Buscar",
DlgFindNotFoundMsg	: "El texto especificado no ha sido encontrado.",

// Replace Dialog
DlgReplaceTitle			: "Reemplazar",
DlgReplaceFindLbl		: "Texto a buscar:",
DlgReplaceReplaceLbl	: "Reemplazar con:",
DlgReplaceCaseChk		: "Coincidir may/min",
DlgReplaceReplaceBtn	: "Reemplazar",
DlgReplaceReplAllBtn	: "Reemplazar Todo",
DlgReplaceWordChk		: "Coincidir toda la palabra",

// Paste Operations / Dialog
PasteErrorCut	: "La configuración de seguridad de este navegador no permite la ejecución automática de operaciones de cortado. Por favor use el teclado (Ctrl+X).",
PasteErrorCopy	: "La configuración de seguridad de este navegador no permite la ejecución automática de operaciones de copiado. Por favor use el teclado (Ctrl+C).",

PasteAsText		: "Pegar como Texto Plano",
PasteFromWord	: "Pegar desde Word",

DlgPasteMsg2	: "Por favor pegue dentro del cuadro utilizando el teclado (<STRONG>Ctrl+V</STRONG>); luego presione <STRONG>OK</STRONG>.",
DlgPasteSec		: "Because of your browser security settings, the editor is not able to access your clipboard data directly. You are required to paste it again in this window.",	//MISSING
DlgPasteIgnoreFont		: "Ignorar definiciones de fuentes",
DlgPasteRemoveStyles	: "Remover definiciones de estilo",
DlgPasteCleanBox		: "Borrar el contenido del cuadro",

// Color Picker
ColorAutomatic	: "Automático",
ColorMoreColors	: "Más Colores...",

// Document Properties
DocProps		: "Propiedades del Documento",

// Anchor Dialog
DlgAnchorTitle		: "Propiedades de la Referencia",
DlgAnchorName		: "Nombre de la Referencia",
DlgAnchorErrorName	: "Por favor, complete el nombre de la Referencia",

// Speller Pages Dialog
DlgSpellNotInDic		: "No se encuentra en el Diccionario",
DlgSpellChangeTo		: "Cambiar a",
DlgSpellBtnIgnore		: "Ignorar",
DlgSpellBtnIgnoreAll	: "Ignorar Todo",
DlgSpellBtnReplace		: "Reemplazar",
DlgSpellBtnReplaceAll	: "Reemplazar Todo",
DlgSpellBtnUndo			: "Deshacer",
DlgSpellNoSuggestions	: "- No hay sugerencias -",
DlgSpellProgress		: "Control de Ortografía en progreso...",
DlgSpellNoMispell		: "Control finalizado: no se encontraron errores",
DlgSpellNoChanges		: "Control finalizado: no se ha cambiado ninguna palabra",
DlgSpellOneChange		: "Control finalizado: se ha cambiado una palabra",
DlgSpellManyChanges		: "Control finalizado: se ha cambiado %1 palabras",

IeSpellDownload			: "Módulo de Control de Ortografía no instalado. ¿Desea descargarlo ahora?",

// Button Dialog
DlgButtonText		: "Texto (Valor)",
DlgButtonType		: "Tipo",
DlgButtonTypeBtn	: "Button",	//MISSING
DlgButtonTypeSbm	: "Submit",	//MISSING
DlgButtonTypeRst	: "Reset",	//MISSING

// Checkbox and Radio Button Dialogs
DlgCheckboxName		: "Nombre",
DlgCheckboxValue	: "Valor",
DlgCheckboxSelected	: "Seleccionado",

// Form Dialog
DlgFormName		: "Nombre",
DlgFormAction	: "Acción",
DlgFormMethod	: "Método",

// Select Field Dialog
DlgSelectName		: "Nombre",
DlgSelectValue		: "Valor",
DlgSelectSize		: "Tamaño",
DlgSelectLines		: "Lineas",
DlgSelectChkMulti	: "Permitir múltiple selección",
DlgSelectOpAvail	: "Opciones disponibles",
DlgSelectOpText		: "Texto",
DlgSelectOpValue	: "Valor",
DlgSelectBtnAdd		: "Agregar",
DlgSelectBtnModify	: "Modificar",
DlgSelectBtnUp		: "Subir",
DlgSelectBtnDown	: "Bajar",
DlgSelectBtnSetValue : "Establecer como predeterminado",
DlgSelectBtnDelete	: "Eliminar",

// Textarea Dialog
DlgTextareaName	: "Nombre",
DlgTextareaCols	: "Columnas",
DlgTextareaRows	: "Filas",

// Text Field Dialog
DlgTextName			: "Nombre",
DlgTextValue		: "Valor",
DlgTextCharWidth	: "Caracteres de ancho",
DlgTextMaxChars		: "Máximo caracteres",
DlgTextType			: "Tipo",
DlgTextTypeText		: "Texto",
DlgTextTypePass		: "Contraseña",

// Hidden Field Dialog
DlgHiddenName	: "Nombre",
DlgHiddenValue	: "Valor",

// Bulleted List Dialog
BulletedListProp	: "Propiedades de Viñetas",
NumberedListProp	: "Propiedades de Numeraciones",
DlgLstStart			: "Start",	//MISSING
DlgLstType			: "Tipo",
DlgLstTypeCircle	: "Círculo",
DlgLstTypeDisc		: "Disco",
DlgLstTypeSquare	: "Cuadrado",
DlgLstTypeNumbers	: "Números (1, 2, 3)",
DlgLstTypeLCase		: "letras en minúsculas (a, b, c)",
DlgLstTypeUCase		: "letras en mayúsculas (A, B, C)",
DlgLstTypeSRoman	: "Números Romanos (i, ii, iii)",
DlgLstTypeLRoman	: "Números Romanos (I, II, III)",

// Document Properties Dialog
DlgDocGeneralTab	: "General",
DlgDocBackTab		: "Fondo",
DlgDocColorsTab		: "Colores y Márgenes",
DlgDocMetaTab		: "Meta Información",

DlgDocPageTitle		: "Título de Página",
DlgDocLangDir		: "Orientación de idioma",
DlgDocLangDirLTR	: "Izq. a Derecha (LTR)",
DlgDocLangDirRTL	: "Der. a Izquierda (RTL)",
DlgDocLangCode		: "Código de Idioma",
DlgDocCharSet		: "Codif. de Conjunto de Caracteres",
DlgDocCharSetCE		: "Central European",	//MISSING
DlgDocCharSetCT		: "Chinese Traditional (Big5)",	//MISSING
DlgDocCharSetCR		: "Cyrillic",	//MISSING
DlgDocCharSetGR		: "Greek",	//MISSING
DlgDocCharSetJP		: "Japanese",	//MISSING
DlgDocCharSetKR		: "Korean",	//MISSING
DlgDocCharSetTR		: "Turkish",	//MISSING
DlgDocCharSetUN		: "Unicode (UTF-8)",	//MISSING
DlgDocCharSetWE		: "Western European",	//MISSING
DlgDocCharSetOther	: "Otra Codificación",

DlgDocDocType		: "Encabezado de Tipo de Documento",
DlgDocDocTypeOther	: "Otro Encabezado",
DlgDocIncXHTML		: "Incluir Declaraciones XHTML",
DlgDocBgColor		: "Color de Fondo",
DlgDocBgImage		: "URL de Imagen de Fondo",
DlgDocBgNoScroll	: "Fondo sin rolido",
DlgDocCText			: "Texto",
DlgDocCLink			: "Vínculo",
DlgDocCVisited		: "Vínculo Visitado",
DlgDocCActive		: "Vínculo Activo",
DlgDocMargins		: "Márgenes de Página",
DlgDocMaTop			: "Tope",
DlgDocMaLeft		: "Izquierda",
DlgDocMaRight		: "Derecha",
DlgDocMaBottom		: "Pie",
DlgDocMeIndex		: "Claves de indexación del Documento (separados por comas)",
DlgDocMeDescr		: "Descripción del Documento",
DlgDocMeAuthor		: "Autor",
DlgDocMeCopy		: "Copyright",
DlgDocPreview		: "Vista Previa",

// Templates Dialog
Templates			: "Plantillas",
DlgTemplatesTitle	: "Contenido de Plantillas",
DlgTemplatesSelMsg	: "Por favor selecciona la plantilla a abrir en el editor<br>(el contenido actual se perderá):",
DlgTemplatesLoading	: "Cargando lista de Plantillas. Por favor, aguarde...",
DlgTemplatesNoTpl	: "(No hay plantillas definidas)",
DlgTemplatesReplace	: "Replace actual contents",	//MISSING

// About Dialog
DlgAboutAboutTab	: "Acerca de",
DlgAboutBrowserInfoTab	: "Información de Navegador",
DlgAboutLicenseTab	: "Licencia",
DlgAboutVersion		: "versión",
DlgAboutInfo		: "Para mayor información por favor dirigirse a"
};
