<?php

namespace Drupal\checklist\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\mantenimiento\Form\editEmpresa;
use http\Client\Curl\User;
use Psy\Exception\Exception;


class checklistController extends ControllerBase {

	public function mostrarChecklists(){
		$contenido = array();

		$contenido['checklist'] =  array(
		  '#markup' => "<h1>listado de checklists</h1>>"
		);

		return $contenido;
	}

	public function mostrarTareasPorproyecto($id_proyecto) {
		
//		$contenido['#attached']['library'][] = 'checklist/tarea_library';

		$contenido = array();

		$proyecto = getRegistro('tp_proyecto', 'id_proyecto', $id_proyecto);

		$contenido['proyecto'] =  array(
			'#markup' => '<h1>'.$proyecto['proyecto'].'</h1>'
		);

		$url_calendario = Url::fromRoute('calendario.proyecto');
		$calendario_link = Link::fromTextAndUrl(t('Ir al calendario'), $url_calendario);
		$calendario_link = $calendario_link->toRenderable();
		$calendario_link['#attributes'] = array('class' => array('btn', 'btn-primary'));

		$contenido['ir_al_calendario'] =  array(
			'#markup' => render($calendario_link),
			'#prefix' => '<div class="botones-cabecera-tabla">',
		);
		
		
	    $url_copy_tarea = Url::fromRoute('checklist.copyTareas', array('id_proyecto' => $id_proyecto));
	    $copyTarea_link = Link::fromTextAndUrl(t('Copiar tareas'), $url_copy_tarea);
	    $copyTarea_link = $copyTarea_link->toRenderable();
	    // If you need some attributes.
	    $copyTarea_link['#attributes'] = array('class' => array('btn', 'btn-primary'));
	
	
	    $contenido['copiar_tareas'] =  array(
	        '#markup' => render($copyTarea_link) ,
	    );


		$url = Url::fromRoute('checklist.addTarea', array('id_proyecto' => $id_proyecto));
		$project_link = Link::fromTextAndUrl(t('Agregar Tarea'), $url);
		$project_link = $project_link->toRenderable();
		// If you need some attributes.
		$project_link['#attributes'] = array('class' => array('btn', 'btn-primary'));


		$contenido['nueva_tarea'] =  array(
		    '#markup' => render($project_link),
			'#suffix' => '</div>'
		);


		$rows = array();
		$rows = listar_tareas($id_proyecto);
		// Build a render array which will be themed as a table with a pager.
		 $contenido['table'] = [
			'#rows' => $rows,
			'#header' => [t('Id Tarea'),
			t('Código'),
			t('Tarea'),
			t('Descripción'),
			t('proyecto'),
			t('Estado'),
			t('SLA'),
			t('Tiempo (min)'),
			t('Pre/Post SLA'),
			t('Fecha Creación'),
			t('Fecha Edición'),
			t('Usuario Creador'),
			t('Usuario Editor'), /*t('Ver'),*/
			t('Ejecutar Acciones'),
			t('Detalle'),
			t('Editar'),
			t('Eliminar')],
			'#type' => 'table',
			'#empty' => t('No content available.'),
		 ];
		 $contenido['pager'] = [
		   '#type' => 'pager',
		   '#weight' => 10,
		 ];

		return $contenido;
	}


	public function detailTarea($id_proyecto ,$id_tarea) {

		global $base_url;
		$connection = \Drupal::database();

		$contenido = array();

		$tarea = getRegistro('tp_tarea', 'id_tarea', $id_tarea);

		$contenido['linea1'] =  array(
			'#markup' => '<h1>Tarea'.$tarea['nombre'].'</h1>',
		);

//		$url = Url::fromUri($base_url.'/proyecto/'.$id_proyecto.'/tareas');
//		$editar_link = \Drupal::l(t('Regresar a la tabla de tareas'), $url);
//		$row['editar'] = $editar_link;
//
//		$contenido['linea2'] =  array(
//			'#markup' => $editar_link.'<br><br>' ,
//		);


		$queryEquipos = $connection->query("SELECT * FROM tp_equipo WHERE estado = 1");
		$resultEquipos = $queryEquipos->fetchAll();

		$equipos = array();
		$usuarios_turno = array();

		foreach ($resultEquipos as $row){
			$row = (array) $row;

			$equipos[$row['id_equipo']] = $row['nombre'];

			$queryUsuarios = $connection->query("SELECT *
			FROM tp_responsable as responsable
			INNER JOIN tp_user_turno as uturno ON responsable.id_responsable = uturno.id_responsable
			WHERE uturno.id_equipo = :id_equipo and uturno.id_tarea = :id_tarea", array('id_equipo' => $row['id_equipo'], 'id_tarea' => $id_tarea));
			$resultUsuarios = $queryUsuarios->fetchAll();

			$utemp = array();
			foreach ($resultUsuarios as $rowU){
				$rowU = (array)$rowU;

				$utemp[$rowU['id_responsable']] = $rowU['username'];
				
			}
			$usuarios_turno[$rowU['id_equipo']] = $utemp;
		}


		$contenido[] = [
			'#theme' => 'checklist_detail_tarea',
			'#equipos' => $equipos,
			'#usuarios_turno' => $usuarios_turno
		];


		return $contenido;

	}


	public function copiar_tareas_proyecto($id_proyecto) {
	
		$contenido = array();
		
		$database = \Drupal::database();
		
		$url_tareas = Url::fromRoute('checklist.tareas', array('id_proyecto' => $id_proyecto));
		$tareas_link = Link::fromTextAndUrl(t('Regresar a tareas del proyecto'), $url_tareas);
		$tareas_link = $tareas_link->toRenderable();
		$tareas_link['#attributes'] = array('class' => array('btn', 'btn-primary'));
		
		$contenido['ir_a_tareas'] =  array(
			'#markup' => render($tareas_link) . '<br><br>' ,
		);
		
		$rows = array();
		$rows = listar_proyectos($id_proyecto);
		// Build a render array which will be themed as a table with a pager.
		$contenido['table'] = [
		  '#rows' => $rows,
		  '#header' => [t('Id proyecto'),
		    t('proyecto'),
		    t('Fecha Transmisión'),
		    t('Duracion'),
		    t('Fecha Finalización'),
		    t('País'),
		    t('Empresa'),
		    t('Copiar sus tareas')],
		  '#type' => 'table',
		  '#empty' => t('No content available.'),
		];
		$contenido['pager'] = [
		  '#type' => 'pager',
		  '#weight' => 10,
		];
		
		return $contenido;
	}

	public function copiar_tareas_proyecto_ejecutar($id_proyecto, $id_proyecto_cp){
	
		$uid_user = \Drupal::currentUser()->id();
		$database = \Drupal::database();
		
		try {
		
			$query = $database->query("SELECT proyecto.id_proyecto, tarea.id_tarea, tarea.codigo, tarea.nombre, tarea.descripcion, proyecto.proyecto, tarea.checkin, tarea.medialive_accion, tarea.medialive_canal, sla.nombre as nombreSLA, sla.tiempo, sla.estado_tiempo, tarea.fecha_creacion, tarea.fecha_edicion, tarea.uid_creador, tarea.uid_editor
			FROM tp_tarea as tarea
			INNER JOIN tp_proyecto as proyecto ON tarea.id_proyecto = proyecto.id_proyecto
			INNER JOIN tp_sla as sla ON tarea.id_tarea = sla.id_tarea
			WHERE tarea.id_proyecto = :id_proyecto and tarea.estado = 1", array('id_proyecto' => $id_proyecto_cp));
			$tareasDB = $query->fetchAll();
		
			global $base_url;
			foreach ($tareasDB as $row) {
				$row = (array) $row;
				
				$campos_tarea = array(
				  'codigo' => $row['codigo'],
				  'nombre' => $row['nombre'],
				  'descripcion' => $row['descripcion'],
				  'id_proyecto' => $id_proyecto,
				  'checkin' => $row['checkin'],
				  'medialive_accion' => $row['medialive_accion'],
				  'medialive_canal' => $row['medialive_canal'],
				  'slack_mensaje' => $row['slack_mensaje'],
				  'slack_canal' => $row['slack_canal'],
				  'slack_img_msj' => $row['slack_img_msj'],
				  'fecha_creacion' => time(),
				  'uid_creador' => $uid_user,
				);
				
				$resulTarea = $database->insert('tp_tarea')
				  ->fields($campos_tarea)
				  ->execute();
				
				
				$campos_sla = array(
				  'nombre' => $row['nombreSLA'],
				  'tiempo' => $row['tiempo'],
				  'estado_tiempo' => $row['estado_tiempo'],
				  'fecha_creacion' => time(),
				  'uid_creador' => $uid_user,
				  'id_tarea' => $resulTarea
				);
				
				$resultSLA = $database->insert('tp_sla')
				  ->fields($campos_sla)
				  ->execute();
				
				
				// GUARDADO DE USUARIOS ENCARGADOS
				
				$queryEquipos = $database->query("SELECT * FROM tp_equipo WHERE estado = 1");
				$resultEquipos = $queryEquipos->fetchAll();
				
				foreach ($resultEquipos as $row){
					$row = (array) $row;
					
					$queryResponsablesproyecto = $database->select('tp_user_turno_default', 'uturno');
					$queryResponsablesproyecto->fields('uturno');
					$queryResponsablesproyecto->condition('uturno.id_equipo', (int)$row['id_equipo'], '=');
					$queryResponsablesproyecto->condition('uturno.id_proyecto', (int)$id_proyecto, '=');
					$resultResponsablesproyecto = $queryResponsablesproyecto->execute()->fetchAll();
					
					foreach ($resultResponsablesproyecto as $u){
						$u = (array) $u;
						
						$campos_usuarios = array(
							'id_equipo' => $row['id_equipo'],
							'id_responsable' => $u['id_responsable'],
							'id_tarea' => $resulTarea,
						);
						
						$insertUturno = $database->insert('tp_user_turno')
							->fields($campos_usuarios)
							->execute();
					}
				}
			
			}
			
			$query_nombre_proyecto_copiador = $database->query("SELECT * FROM tp_proyecto WHERE id_proyecto = :id_proyecto", array('id_proyecto' => $id_proyecto));
			$result_proyecto_copiador = $query->fetchAssoc();
			
			
			$query_nombre_proyecto_copiado = $database->query("SELECT * FROM tp_proyecto WHERE id_proyecto = :id_proyecto", array('id_proyecto' => $id_proyecto_cp));
			$result_proyecto_copiado = $query->fetchAssoc();
		
		    drupal_set_message('Se han copiado las tareas del proyecto: "'.$result_proyecto_copiado['proyecto'].'" al a su proyecto "'.$result_proyecto_copiador['proyecto'].'"');
		
		}catch (Exception $e){
		
		    drupal_set_message($e->getMessage(), 'error');
		
		}
		
		return  $this->redirect('checklist.tareas', array('id_proyecto' => $id_proyecto));
	
	}

}


function listar_tareas($id_proyecto) {
    $database = \Drupal::database();


//    $query = $database->query("SELECT proyecto.id_proyecto,
//    tarea.id_tarea,
//    tarea.codigo,
//    tarea.nombre,
//    tarea.descripcion,
//    proyecto.proyecto,
//    tarea.checkin,
//    tarea.medialive_accion,
//    tarea.medialive_canal,
//    sla.nombre as nombreSLA,
//    sla.tiempo,
//    sla.estado_tiempo,
//    tarea.fecha_creacion,
//    tarea.fecha_edicion,
//    tarea.uid_creador,
//    tarea.uid_editor
//    FROM tp_tarea as tarea
//    INNER JOIN tp_proyecto as proyecto ON tarea.id_proyecto = proyecto.id_proyecto
//    INNER JOIN tp_sla as sla ON tarea.id_tarea = sla.id_tarea
//    WHERE tarea.id_proyecto = :id_proyecto and tarea.estado = 1", array('id_proyecto' => $id_proyecto));
//    $tareasDB = $query->fetchAll();


    $query = $database->select('tp_tarea', 'tarea');
    $query->join('tp_proyecto', 'proyecto', 'tarea.id_proyecto = proyecto.id_proyecto');
    $query->join('tp_sla', 'sla', 'tarea.id_tarea = sla.id_tarea');
    
    $query->fields('tarea', array('id_tarea', 'codigo', 'nombre', 'descripcion'));
    $query->fields('proyecto', array('id_proyecto', 'proyecto'));
//    $query->fields('tarea', array(
//    	'checkin',
//	    'medialive_accion',
//	    'medialive_canal',
//	    'slack_mensaje',
//	    'slack_canal',
//	    'id_proyecto_jira',
//	    'jira_summary',
//	    'jira_description',
//	    'jira_email',
//	    'jira_apitoken'
//    ));
	$query->addField('sla', 'nombre', 'nombreSLA');
    $query->fields('sla', array('tiempo', 'estado_tiempo'));
    $query->fields('tarea', array('fecha_creacion', 'fecha_edicion', 'uid_creador', 'uid_editor'));
    $query->condition('tarea.id_proyecto', $id_proyecto, '=');
    $query->condition('tarea.estado', '1', '=');
    $page = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $tareasDB = $page->execute()->fetchAll();

//    $queryTareas = $database->select('tp_empresa', 'empresa')
//      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(5);
//    $queryTareas->fields('empresa');
//    $queryTareas->condition('empresa.estado', '1', '=');
//
//    $result = $queryTareas->execute();

    $rows = [];

    global $base_url;
    foreach ($tareasDB as $row) {

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

        $id_proyecto = $row['id_proyecto'];
        unset($row['id_proyecto']);

        if ($row['checkin'] == 1){
          $row['checkin'] = "Realizada";
        }else{
          $row['checkin'] = "Pendiente";
        }

        switch ($row['estado_tiempo']){
			case 1:
				$row['estado_tiempo'] = "Pre";
				break;

			case 2:
				$row['estado_tiempo'] = "Pos";
				break;
        }



//        $row['ejecutar'] = $acciones_link;
//        $row['finalizar'] = $finalizar_link;
        $row['sin_accion'] = "";
		

	    $url = Url::fromUri($base_url.'/proyecto/'.$id_proyecto.'/tarea/'.$row['id_tarea'].'/detail', array('attributes' => [
		    'class' => ['use-ajax', 'ver'],
		    'data-dialog-type' => 'modal',
		    'data-dialog-options' => Json::encode([
			    'width' => 800,
		    ]),
	    ]));
	    $detalle_link = \Drupal::l(t('Ver Detalle'), $url);
	    $row['detalle']=$detalle_link;

        $url = Url::fromUri($base_url.'/proyecto/'.$id_proyecto.'/tarea/'.$row['id_tarea'].'/editar', array('attributes' => array('class' => 'editar')));
        $editar_link = \Drupal::l(t('Editar'), $url);
        $row['editar']=$editar_link;
	
	    $url = Url::fromUri($base_url.'/proyecto/'.$id_proyecto.'/tarea/'.$row['id_tarea'].'/delete', array('attributes' => [
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

    return $rows;

}


function listar_proyectos($id_proyecto) {
  $database = \Drupal::database();

  $queryET = $database->query("SELECT DISTINCT proyecto.id_proyecto, proyecto.proyecto, proyecto.fecha_transmision, proyecto.duracion, proyecto.fecha_finalizacion, proyecto.id_pais, proyecto.id_empresa FROM tp_proyecto as proyecto
INNER JOIN tp_tarea as tarea ON proyecto.id_proyecto = tarea.id_proyecto
WHERE proyecto.estado = 1");
  $resultET = $queryET->fetchAll();

//  $query = $database->select('tp_proyecto', 'proyecto')
//    ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(5);
//  $query->fields('proyecto', array('id_proyecto','proyecto', 'fecha_transmision', 'duracion', 'fecha_finalizacion', 'id_pais', 'id_empresa'));
//  $query->condition('proyecto.estado', '1', '=');
//  $result = $query->execute();

  $rows = [];

  global $base_url;
  foreach ($resultET as $row) {
    $row = (array) $row;

    $queryTareas = \Drupal::database()->select('tp_tarea', 'tarea')
      ->condition('id_proyecto', $row['id_proyecto'], '=');
    $queryTareas->addExpression('COUNT(*)');
    $count = $queryTareas->execute()->fetchField();

    if ($count > 0){
      $pais = getRegistro('tp_pais', 'id_pais', $row['id_pais']);
      $empresa = getRegistro('tp_empresa', 'id_empresa', $row['id_empresa']);

      $row['id_pais'] = $pais['nombre'];
      $row['id_empresa'] = $empresa['nombre'];

      $url = Url::fromUri($base_url.'/proyecto/'.$id_proyecto.'/proyecto-tareas/'.$row['id_proyecto'].'/copiar');
      $accion_link = \Drupal::l(t('Copiar'), $url);
      $row['accion'] = $accion_link;

      $rows[] =  $row;
    }


  }

  return $rows;

}
