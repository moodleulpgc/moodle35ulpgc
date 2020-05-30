<?php


// Script PHP con las consultas más comunes al Oracle

if (! defined('CLI_SCRIPT')) {
    define('CLI_SCRIPT', true);
}
 
require_once (__DIR__ . '/../../../config.php');
require_once ('../locallib.php');
 
global $CFG, $DB, $USER;
 
$extdb = db_init();
 
/*
1.- Listado de usuarios a sincronizar
2.- Listado de cargos administrativos
3.- Datos personales de usuarios
4.- Listado de cursos
5.- Listado de enrolamientos
6.- Grupos de un usuario
7.- Listado de categorías
8.- Grupos de un curso
*/
$opcion=5;
 
 
switch($opcion){
  case 1:
    //Listado de usuarios a sincronizar
        $sqlusuariosulpgc = "SELECT lower(u.username) AS username, MAX(up.estado) AS estado, min(m.rol) AS rol
                               FROM tmousuariosplataforma up, tmousuarios u, tmoplataformasactivas p, tmomatriculas m
                              WHERE p.plataforma = '{$CFG->plataforma}'
                                    AND p.aacada = '{$CFG->aacada}'
                                    AND up.plataformaid = p.id
                                    AND u.id = up.usuarioid
                                    AND m.usuarioid = u.id";
 
        /* Solo se crean las cuentas de los usuarios cuando no está habilitada la carga de alumnos */
        if (! $CFG->cargaalumnos) {
            $sqlusuariosulpgc .= " AND EXISTS (select id from tmomatriculas m where m.usuarioid = u.id AND m.rol like '%teacher%' and estado='I')";
        }
 
        $sqlusuariosulpgc .= " GROUP BY lower(u.username)";
 
        $consulta = get_rows_external_db($extdb, $sqlusuariosulpgc, 'username');
  break;
 
  case 2:
      // Obtención de los cargos administrativos de cada unidad
      $sqlunidadesulpgc = " SELECT codigo AS idnumber, denominacion AS name, tipo,
        PACKUSUARIOS_API.IDAUSUARIO( directorid ) AS director ,
        PACKUSUARIOS_API.IDAUSUARIO( secretarioid ) AS secretary,
        estado FROM tmounidades u WHERE estado='I'";
      $consulta = get_rows_external_db($extdb, $sqlunidadesulpgc, 'idnumber');
  break;
 
  case 3:
      // Obtención de datos personales de usuarios
      $sqldatos = "SELECT * FROM vmodatospersonales v WHERE lower(username) like '7847%' AND email like '%@ulpgc.es'";
      $consulta = get_rows_external_db($extdb, $sqldatos, 'username');
  break;
 
  case 4:
      // Obtención del listado de cursos
      $sqlcursosulpgc = "SELECT c.shortname, c.aadenc AS fullname, c.ccuatrim AS term, ca.idnumber AS category, c.idnumber, c.creditos AS credits,
                    u.codigo AS departamento, c.estado
                     FROM tmocursos c, tmocategorias ca, tmoplataformasactivas p, tmounidades u
                    WHERE ca.id = c.categoriaid
                      AND p.id = ca.plataformaid
                      AND u.id(+) = c.departamentoid
                      AND u.tipo(+) = 'departamento'
                      AND p.plataforma = '{$CFG->plataforma}'
                      AND vinculo IS NULL";
       $consulta = get_rows_external_db($extdb, $sqlcursosulpgc, 'idnumber');
  break;
 
  case 5:
      // Obtencion de los enrolamientos
       $sqlenrolsulpgc = "SELECT lower(u.username || '|' || c.idnumber || '|' || m.rol) as enrol, u.username, c.idnumber, m.rol, m.estado
                        FROM tmomatriculas m, tmocursos c, tmoplataformasactivas p, tmocategorias ca, tmousuarios u
                       WHERE p.plataforma = '{$CFG->plataforma}'
                             AND p.aacada = '{$CFG->aacada}'
                                         AND ca.plataformaid = p.id
                                         AND c.categoriaid = ca.id
                                         AND m.cursoid = c.id
                                         AND u.id = m.usuarioid
                                ORDER BY c.idnumber,m.estado desc";
        $consulta = get_rows_external_db($extdb, $sqlenrolsulpgc, 'enrol');
  break;
 
case 6:
      // Obtencion de los Grupos de un usuario
       $sqlasignacionesulpgc = "SELECT lower(u.username) || '|' || c.idnumber || '|' || g.cod_grupo AS asignacion, u.username, c.idnumber, g.cod_grupo, gu.estado
                               FROM tmoplataformasactivas p, tmocursos c, tmocategorias ca, tmogrupos g, tmogruposusuarios gu, tmousuarios u
                              WHERE p.aacada = '$CFG->aacada'
                                    AND p.plataforma = '$CFG->plataforma'
                                    and ca.plataformaid = p.id
                                    and c.categoriaid = ca.id
                                    and g.cursoid = c.id
                                    and gu.grupoid = g.id
                                    and u.id = gu.usuarioid
                                    and u.username='45393950'
                             ORDER BY 1";
 
 
        $consulta = get_rows_external_db($extdb, $sqlasignacionesulpgc, 'asignacion');
  break;
 
case 7:
      // Obtención de las categorías
      $sqlcategoriasulpgc = "SELECT denominacion AS name, idnumber, superior, estado
               FROM tmocategorias ca, tmoplataformasactivas p
               WHERE p.id = ca.plataformaid
               AND p.plataforma = '{$CFG->plataforma}'
               ORDER BY nivel ASC";
      $consulta = get_rows_external_db($extdb, $sqlcategoriasulpgc, 'idnumber');
  break;

case 8:
      // Obtencion de los Grupos de un usuario
       $sqlasignacionesulpgc = "SELECT lower(u.username) || '|' || c.idnumber || '|' || g.cod_grupo AS asignacion, u.username, c.idnumber, g.cod_grupo, gu.estado
                               FROM tmoplataformasactivas p, tmocursos c, tmocategorias ca, tmogrupos g, tmogruposusuarios gu, tmousuarios u
                              WHERE p.aacada = '$CFG->aacada'
                                    AND p.plataforma = '$CFG->plataforma'
                                    and ca.plataformaid = p.id
                                    and c.categoriaid = ca.id
                                    and g.cursoid = c.id
                                    and gu.grupoid = g.id
                                    and u.id = gu.usuarioid
                                    and c.shortname='555165'
                             ORDER BY 1";


        $consulta = get_rows_external_db($extdb, $sqlasignacionesulpgc, 'asignacion');
  break;


 
}
 
db_close($extdb);
print_r ($consulta);
 
?>


