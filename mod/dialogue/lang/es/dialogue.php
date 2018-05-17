<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

$string['firstname'] = 'Nombre de pila';
$string['latname'] = 'Apellido';
$string['oldest'] = 'Más antiguo';
$string['displaybystudent'] = 'Interlocutor';


/// ULPGC special strings lang es

// ecastro ULPGC
$string['multipleconversations'] = 'Conversaciones múltiples'; 
$string['multipleconversations_help'] = 'Este parámetro controla si un usuario puede mantener varias conversaciones simultáneas con el mismo interlocutor.

Si se ajusta en NO, entonces un usuario sólo podrá establecer una única conversación abierta con cada interlocutor.
Las conversaciones ya cerradas no cuentan para este límite.
Los Profesores no son afectados por esta restricción. '; 
$string['nomatchingpeoplemultiple'] = 'No hay más personas sin conversaciones ya abiertas: use una de las conversaciones existentes.';
$string['replyby'] = 'Respuesta: {$a}';
$string['any'] = 'Todos';
$string['replied'] = 'Respondidos';
$string['unreplied'] = 'No respondidos';
$string['viewanyby'] = 'Participación: {$a}';
$string['own'] = 'Si';
$string['other'] = 'No';
$string['dialogue:openasstaff'] = 'Abrir conversación como Profesor';
$string['dialogue:receiveasstaff'] = 'Recibir conversación como Profesor';
$string['alternatemode'] = 'Modo alterno';
$string['alternatemode_help'] = 'Cuando se activa el modo alterno los estudiantes y profesores se envían mensajes unos a otros, pero no entre si.
Los estudiantes que abren un Diálogo pueden elegir solo de enter sus profesores como destinatarios del emnsaje, y viceversa. <br />

Los dos grupos que se alternan están definidos por permisos específicos. Si el usuario que abre un Diálogo tiene la capacidad
"Abrir como Profesor" entonces los potencials destinatarios serán todos los que tengan la capaciadad "Recibir un mensaje".
Por el contrario, si eso no es así ("Abrir" normal, estudiantes) entonces los destinatarios potenciales serán aquellos
con la capacidad "Recibir como Profesor" (típicamente, solo los profesores). ';
$string['notifications'] = 'Notificación por e-mail';
$string['notifications_help'] = 'Si se activa, entonces se remitirá una notificación por correo electrónico de cada mensaje en una conversación al destinatario del mismo..';
$string['sendkeep'] = 'Enviar y seguir';
$string['configtrackunreplied'] = 'Usar no-respondidos en lugar de no-leídos en la pagina del curso.';
$string['editmessage'] = 'Edit message';
$string['unrepliedmessagesnumber'] = '{$a} mensajes no respondidos';
$string['unrepliedmessagesone'] = '1 mensaje no respondido';
$string['configeditingtime'] = 'Permitir un periodo de gracia para edición como en Foro.';

$string['completionconversations'] = 'Es usuario debe empezar conversaciones:';
$string['completionconversationsgroup'] = 'Requerir conversaciones';
$string['completionconversationshelp'] = 'se requieren conversaciones para completar';
$string['completionposts'] = 'El usuario debe enviar mensajes:';
$string['completionpostsgroup'] = 'Requerir mensajes';
$string['completionpostshelp'] = 'se requieren mensajes para completar';
$string['completionreplies'] = 'El usuario debe enviar réplicas a otros:';
$string['completionrepliesgroup'] = 'Requerir repuestas';
$string['completionreplieshelp'] = 'se requieren réplicas para completar';
$string['dialoguecronnotifications'] = 'Envío de correos de notificaciones demoradas';
$string['viewconversation'] = 'Ver conversación';
