<?php
define ( 'CLI_SCRIPT', true );
$dir = __DIR__;
require_once ($dir . '/../../../config.php');
$pp = $CFG->plataforma;

// La información a sincronizar depende de la plataforma
// En las plataformas de desarrollo (pruebas) no se añade información académica
if ($pp != 'pruebas') {
	// La información de CENTROS y DEPARTAMENTOS se incluye en Grado y Posgrado
	if ($pp == 'tp') {
		// Sincronización de Unidades (Centros y Departamentos)
		include 'sincunidades.php';
		echo "Sincro de UNIDADES finalizada\n";
	}
    // Se sincronizan CATEGORÍAS Y CURSOS, excepto en Teletrabajo
	if ($pp != 'tt') {
		// Sincronización de CATEGORÍAS
		include $dir . '/sinccategorias.php';
		echo "Sincro de CATEGORÍAS finalizada\n";

		// Sincronización de CURSOS
		include $dir . '/sinccursos.php';
		echo "Alta de CURSOS finalizada\n";
	}

	// Se sincronizan GRUPOS en Grado y Posgrado, Teleformación, Social
	if ($pp != 'tt' && $pp != 'oe') {
		// Sincronización de GRUPOS
		include $dir . '/sincgrupos.php';
		echo "Sincro de GRUPOS finalizada\n";
                // Sincronización de cohortes
                include $dir . '/sinccohortes.php';
                echo "Sincro de COHORTES finalizada\n";
	}

	// Se sincronizan USUARIOS, excepto en Teletrabajo
	if ($pp != 'tt') {
        // Sincronización de USUARIOS
        include $dir . '/sincusuarios.php';
        echo "Sincro de USUARIOS finalizada\n";
        // Sincronización de MATRÍCULAS
        include $dir . '/sincenrol.php';
        echo "Sincro de MATRÍCULAS finalizada\n";
	}

	// Se sincronizan ASIGNACIONES en Grado y Posgrado, Teleformación, Social
	if ($pp != 'tt' && $pp != 'oe' ) {
		// Sincronización de ASIGNACIONES de grupos
		include $dir . '/sincagrupar.php';
		echo "Sincro de ASIGNACIONES de GRUPOS finalizada\n";
                // Sincronización de ASIGNACIONES de cohortes
                include $dir . '/sincagruparcohortes.php';
                echo "Sincro de ASIGNACIONES de COHORTES finalizada\n";
	}
}
?>
