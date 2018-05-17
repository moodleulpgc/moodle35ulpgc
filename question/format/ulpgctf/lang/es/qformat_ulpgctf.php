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

/**
 * Strings for component 'qformat_ulpgctf', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qformat_ulpgctf
 * @copyright  2014 Enrique Castro ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Formato ULPGC-TF (AIKEN modificado)';
$string['pluginname_help'] = 'Es un formato muy simple para importar preguntas de opción múltiple con la página del manual.
Las preguntas deben escribirse en un archivo de texto en este formato: <br />

Enunciado de la pregunta en un párrafo más o menos largo pero sólo uno.<br />
A) opción de respuesta  1 (cada una en un párrafo individual, sin líneas intermedias) <br />
B) opción de respuesta  2<br />
C) opción de respuesta  3<br />
D) opción de respuesta  4<br />
NAME: nombre de la pregunta (opcional, puede  omitirse)<br />
PG: 25, página de manual (puede ser un texto de varios números y palabras)<br />
ANSWER: B<br />


La pregunta debe terminar con la palabra clave ANSWER: y la letra (mayúscula) de la opción correcta. Esto es imprescindible.

';
$string['pluginname_link'] = 'qformat/ulpgctf';
