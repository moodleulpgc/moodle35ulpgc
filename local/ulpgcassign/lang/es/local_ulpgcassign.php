<?php
/**
 * Cadenas de texto de la extensión de sincronización de la ULPGC
 *
 * @package local_ulpgcassign
 * @copyright  2016 Enrique Castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Tareas ULPGC';
$string['[ulpgcassign:manage'] = 'Gestionar Tareas ULPGC';

// settings
$string['assignsettings'] = 'Tareas ULPGC';
$string['advancedassigns'] = 'Habilitar interfaz avanzado';
$string['explainadvancedassigns'] = 'Si se activa entonces las páginas de Tarea usarán un interfaz con herramientas adicionales.';


$string['filtered'] = 'Mostrando usuerios según criterio: ';
$string['filteredout'] = 'Hay {$a} usuarios no mostrados por este filtro';
$string['filterdraft'] = 'Borrador';
$string['filtergraded'] = 'Calificados';
$string['filternotgraded'] = 'NO calificados';
$string['filterblocked'] = 'Bloqueados';
$string['filterdueextended'] = 'Plazo ampliado';
$string['filtersubmittedlate'] = 'Entrega retrasada';
$string['membertimemodified'] = 'Última modificación por usted'; 
$string['modifiedaftermsg'] = 'La entrega grupal ha sido modificada <strong>después</strong> de su confirmación. 
Puede editar la entrega para volver a cambiarla o confirmar otra vez si está de acuerdo con esta versión.'; 
$string['colorsubmitted'] = 'Color "Enviado para calificar"';
$string['explaincolorsubmitted'] = 'Color de fondo para los textos que indican que una Tarea ha sido entregada. ';
$string['colorsubmitted_graded'] = 'Color "Enviado y Calificado"';
$string['explaincolorsubmitted_graded'] = 'Color de fondo para los textos "Enviado para calificar", 
una vez que la Tarea ya ha sido debidamente calificada. ';
$string['colorgraded'] = 'Color "Calificado"';
$string['explaincolorgraded'] = 'Color de fondo para la notas de texto "Calificado" cuando una tarea se ha corregido y añadido una puntuación. ';

$string['gradeusernewui'] = 'Editar y calificar entrega de {$a} (nuevo panel)';
$string['gradenewui'] = 'Calificar en nuevo panel';
$string['updategradenewui'] = 'Actualizar en nuevo panel';
$string['allownewui'] = 'Utiliza panel de corrección';
$string['configallownewui'] = 'Permite definir qué tipo de entregas usarán el nuevo (3.1) interfaz con panel de corrección'; 

// assignfeedback_offline
$string['gradeignore'] = 'Calificación no reconocida - {$a->grade} - para {$a->student} '; 
$string['onlineassess'] = 'Corregir en web'; 
