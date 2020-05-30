<?php

namespace Drupal\calendario\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Url;
use Drupal\Core\Link;
use http\Client\Curl\User;


class calendarioController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */

//   public function showPais($arg) {
//
//     global $base_url;
//
//     $contenido = array();
//
//     $url = Url::fromUri($base_url.'/mantenimiento/'.$arg.'/edit');
//     $editar_link = \Drupal::l(t('Editar registro'), $url);
//     $row['editar'] = $editar_link;
//
//     $contenido['linea2'] =  array(
//      '#markup' => $editar_link.'<br><br>' ,
//     );
//
//     $registro = array();
//     $registro = editEmpresa::Listarunregistro($arg);
//     ksm($registro);
//
//     $contenido[] = [
//        '#theme' => 'mantenimiento_template',
//        '#test_var' => $registro,
//      ];
//
//
//     return $contenido;
//
//   }


	public function mostrarCalendario() {
	
		$contenido = array();
		
		// LINKS MODALES
//		$contenido['link_modal_importar'] = array(
//		  '#type' => 'link',
//		  '#title' => $this->t('Importar archivo CSV'),
//		  '#url' => Url::fromRoute('calendario.importproyecto', ['node_type' => 'content_type_movie']),
//		  '#attributes' => [
//			  'class' => ['use-ajax', 'btn', 'btn-primary'],
//			  'data-dialog-type' => 'modal',
//			  'data-dialog-options' => Json::encode([
//				  'width' => 'auto',
//				  'height' => 'auto'
//			  ]),
//		  ],
//			'#prefix' => '<div class="botones-cabecera-tabla">',
//		);

		$contenido['caja_botones'] = array(
		    '#type' => 'markup',
            '#markup' => '<div class="botones-cabecera-tabla">'
        );

//		$contenido['link_modal_agregar'] = array(
//			'#type' => 'link',
//			'#title' => $this->t('Agregar proyecto'),
//			'#url' => Url::fromRoute('calendario.addProyecto', ['node_type' => 'content_type_movie']),
//			'#attributes' => [
//				'class' => ['use-ajax', 'btn', 'btn-primary'],
//				'data-dialog-type' => 'modal',
//				'data-dialog-options' => Json::encode([
//					'width' => 'auto',
//					'height' => 'auto',
//					'overflow' => 'hidden'
//				]),
//			],
//			'#suffix' => '</div>',
//		);
		// ---

        $url = Url::fromRoute('calendario.addProyecto');
        $project_link = Link::fromTextAndUrl(t('Agregar proyecto'), $url);
        $project_link = $project_link->toRenderable();
        $project_link['#attributes'] = array('class' => array('btn', 'btn-primary'));


        $contenido['link_agregar'] =  array(
            '#markup' => render($project_link),
            '#suffix' => '</div>'
        );
		
		
		
		$rows = array();
		$rows = listar_proyectos();
	
		
		$header = array(
			'Id Proyecto',
			'Fecha Iniciación',
			'Duración Estimada',
			'Fecha Finalización',
			'Nombre del Proyecto',
			'Pais'
		);
		
		$database = \Drupal::database();
		
		$query = $database->query("
		SELECT turno.id_turno, turno.nombre
		FROM tp_turno as turno
		WHERE turno.estado = 1");
		$turnos = $query->fetchAll();
		
		$turnos_array = array();
		
		foreach ($turnos as $t){
		  $t = (array) $t;
		  $turnos_array[$t['id_turno']] = $t['nombre'];
		  array_push($header, "Turno ".$t['nombre']);
		}
		
		$header2 = array('Comentario',
		  'Estado',
		  'Fecha Creación',
		  'Fecha Edición',
		  'Usuario Creador',
		  'Usuario Editor',
		  'Ver Tareas',
		  'Editar',
		  'Eliminar');
		
		$header_total = array_merge($header, $header2);
		
		 $contenido['table'] = [
		   '#rows' => $rows,
		   '#header' => $header_total,
		   '#type' => 'table',
		   '#empty' => t('No content available.'),
		 ];
		 $contenido['pager'] = [
		   '#type' => 'pager',
		   '#weight' => 10,
		 ];
		
		return $contenido;
	}

}


function listar_proyectos() {
    $database = \Drupal::database();


  $query = $database->select('tp_proyecto', 'proyecto');
  $query->join('tp_pais', 'pais', 'proyecto.id_pais = pais.id_pais');
//  $query->join('tp_canal', 'canal', 'proyecto.id_canal = canal.id_canal');
  $query->addField('pais', 'nombre', 'nombrePais');
  $query->fields('proyecto');
  $query->fields('pais');
//  $query->fields('canal');
  $query->condition('proyecto.estado', '1', '=');
  $page = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
  $proyectos = $page->execute()->fetchAll();


    $rows = [];

    global $base_url;
    foreach ($proyectos as $row) {
        $row = (array) $row;
        $usuario_creador = \Drupal\user\Entity\User::load($row['uid_creador']);

        if (!empty($row['uid_editor'])){
          $usuario_editor = \Drupal\user\Entity\User::load($row['uid_editor']);
          $row['uid_editor'] = $usuario_editor->get('name')->value;
        }

        $row['fecha_creacion'] = date('d/m/Y', (int)$row['fecha_creacion']);

        if (!empty($row['fecha_edicion'])){
          $row['fecha_edicion'] = date('d/m/Y', (int)$row['fecha_edicion']);
        }

        $row['uid_creador'] = $usuario_creador->get('name')->value;

        unset($row['estado']);
//        unset($row['canal']);
	
//	    $row['tareas'] = array(
//		    '#type' => 'link',
//		    '#title' => t('Importar archivo CSV'),
//		    '#url' => Url::fromUri($base_url.'/proyecto/'.(int)$row['id_proyecto'].'/tareas'),
//		    '#attributes' => [
//			    'class' => ['use-ajax', 'btn', 'btn-primary', 'tareas'],
//			    'data-dialog-type' => 'modal',
//			    'data-dialog-options' => Json::encode([
//				    'width' => 800,
//			    ]),
//		    ],
//	    );
     
        $url = Url::fromUri($base_url.'/proyecto/'.(int)$row['id_proyecto'].'/tareas', array('attributes' => array('class' => 'tareas')));
        $tareas_link = \Drupal::l(t('Tareas'), $url);
        $row['tareas']=$tareas_link;

        /*$url = Url::fromUri($base_url.'/calendario/proyecto/'.(int)$row['id_proyecto'].'/edit', array('attributes' => [
	        'class' => ['use-ajax', 'editar'],
	        'data-dialog-type' => 'modal',
	        'data-dialog-options' => Json::encode([
		        'width' => 'auto',
		        'height' => 'auto'
	        ]),
        ]));
        $editar_link = \Drupal::l(t('Editar'), $url);
        $row['editar']=$editar_link;*/

        $url = Url::fromUri($base_url.'/calendario/proyecto/'.(int)$row['id_proyecto'].'/edit', array('attributes' => array('class' => 'editar')));
        $editar_link = \Drupal::l(t('Editar'), $url);
        $row['editar']=$editar_link;

        $url = Url::fromUri($base_url.'/calendario/proyecto/'.(int)$row['id_proyecto'].'/delete', array('attributes' => [
	        'class' => ['use-ajax', 'eliminar'],
	        'data-dialog-type' => 'modal',
	        'data-dialog-options' => Json::encode([
		        'width' => 'auto',
		        'height' => 'auto'
	        ]),
        ]));
        $eliminar_link = \Drupal::l(t('Eliminar'), $url);
        $row['eliminar']=$eliminar_link;

        $rows[] =  $row;
    }

    // CREANDO ARRAY CALENDARIO

    $queryTurnos = $database->query("
        SELECT turno.id_turno, turno.nombre
        FROM tp_turno as turno
        WHERE turno.estado = 1");
    $turnos = $queryTurnos->fetchAll();

    $turnos_array = array();
    $header_turnos = array();

    foreach ($turnos as $t){
      $t = (array) $t;
      $turnos_array[$t['id_turno']] = $t['nombre'];
      array_push($header_turnos, "Turno ".$t['nombre']);
    }

    $proyectos_calendario = array();

    foreach ($rows as $row){
        $array_row_proyecto = array();

        $array_row_proyecto['id_proyecto'] = $row['id_proyecto'];
        $array_row_proyecto['fecha_transmision'] = date('d/m/Y H:i', (int)$row['fecha_transmision']);
        $array_row_proyecto['duracion'] = $row['duracion'];
	    $array_row_proyecto['fecha_finalizacion'] = date('d/m/Y H:i', (int)$row['fecha_finalizacion']);
        $array_row_proyecto['proyecto'] = $row['proyecto'];
        $array_row_proyecto['pais'] = $row['nombrePais'];

        $turnos_usuarios = array();

        foreach ($turnos_array as $turno_key => $turno_value){

            // usuarios por turno
            $queryTurnos = $database->query("
              SELECT uturno.uid
              FROM tp_user_turno as uturno
              WHERE uturno.id_turno = :id_turno and uturno.id_proyecto = :id_proyecto", [':id_turno' => $turno_key, ':id_proyecto' => $row['id_proyecto']]);
            $usuarios_turno = $queryTurnos->fetchAll();

            $uids = array();
            $uturnos_cadena = "";

            $usuariosTurno = array();

            $uids = array();

            foreach ($usuarios_turno as $uturno){

              $uturno = (array)$uturno;
              $usuario = \Drupal\user\Entity\User::load((int)$uturno['uid']);
              $nombre_usuario = $usuario->get('name')->value;

              array_push($usuariosTurno, $nombre_usuario);

//              ksm($uids);

            }


            $usuariosString = implode('/', $usuariosTurno);


            $turnos_usuarios[$turno_value] = $usuariosString;

            $array_row_proyecto[$turno_value] = $usuariosString;
        }

//        foreach ($turnos_usuarios as $uturnos_key => $uturno_value){
//          $array_row_proyecto[$uturnos_key] = $uturno_value;
//        }

        $array_row_proyecto['comentario'] = "falta agregar comentario";
	    $array_row_proyecto['estado_transmision'] = ((int)$row['estado_transmision'] == 1) ? "Transmitido" : "Por transmitir";
		$array_row_proyecto['fecha_creacion'] = $row['fecha_creacion'];
		$array_row_proyecto['fecha_edicion'] = $row['fecha_edicion'];
		$array_row_proyecto['uid_creador'] = $row['uid_creador'];
		$array_row_proyecto['uid_editor'] = $row['uid_editor'];
		$array_row_proyecto['tareas'] = $row['tareas'];
		$array_row_proyecto['editar'] = $row['editar'];
		$array_row_proyecto['eliminar'] = $row['eliminar'];

        array_push($proyectos_calendario, $array_row_proyecto); // inserto proyecto en el array de calendario
    }

//    ksm($rows);
    return $proyectos_calendario;

}
