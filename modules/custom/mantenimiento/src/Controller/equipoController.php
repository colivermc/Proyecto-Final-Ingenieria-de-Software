<?php

namespace Drupal\mantenimiento\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Url;
use Drupal\Core\Link;
//use Drupal\mantenimiento\Form\editPais;
use http\Client\Curl\User;


class equipoController extends ControllerBase {

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


  public function mostrarEquipos() {

    $contenido = array();

    $url = Url::fromRoute('mantenimiento.addEquipo');
    $project_link = Link::fromTextAndUrl(t('Agregar Equipo'), $url);
    $project_link = $project_link->toRenderable();
    // If you need some attributes.
    $project_link['#attributes'] = [
		  'class' => ['use-ajax', 'btn', 'btn-primary'],
		  'data-dialog-type' => 'modal',
		  'data-dialog-options' => Json::encode([
			  'width' => 'auto',
			  'height' => 'auto'
		  ]),
	  ];

    $contenido['linea2'] =  array(
        '#markup' => render($project_link) . '<br><br>' ,
	    '#prefix' => '<div class="botones-cabecera-tabla">',
	    '#suffix' => '</div>'
    );

    $rows = array();
    $rows = listar_equipos();
    //ksm(listar());
    // Build a render array which will be themed as a table with a pager.
     $contenido['table'] = [
       '#rows' => $rows,
       '#header' => [t('Id Equipo'),
	       t('Nombre'),
	       t('Usuarios'),
	       t('Fecha Creación'),
	       t('Fecha Edición'),
	       t('Usuario Creador'),
	       t('Usuario Editor'),
	       t('Estado'),
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

}


function listar_equipos() {
    $database = \Drupal::database();
	
	$query = $database->select('tp_equipo', 'equipo')
		->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
	$query->fields('equipo');
	$query->condition('equipo.estado', '1', '=');
	$result = $query->execute();
	
    $rows = [];

    global $base_url;
    foreach ($result as $row) {
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
	
	    $estado = (int)$row['estado'];
	    unset($row['estado']);
	    $row['estado'] = ($estado == 1) ? "Activo" : "Inactivo";

        $url = Url::fromUri($base_url.'/equipo/'.(int)$row['id_equipo'].'/edit', array('attributes' => [
	        'class' => ['use-ajax', 'editar'],
	        'data-dialog-type' => 'modal',
	        'data-dialog-options' => Json::encode([
		        'width' => 'auto',
		        'height' => 'auto'
	        ]),
        ]));
        $editar_link = \Drupal::l(t('Editar'), $url);
        $row['editar']=$editar_link;

        $url = Url::fromUri($base_url.'/equipo/'.(int)$row['id_equipo'].'/delete', array('attributes' => [
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
    
    $equipoArray = array();
	
	foreach ($rows as $row) {
		$array_equipo = array();
		
		$array_equipo['id_equipo'] = $row['id_equipo'];
		$array_equipo['nombre'] = $row['nombre'];
		
		
		$queryUsuarios = $database->query("SELECT * FROM tp_user_equipo
 		WHERE id_equipo = :id_equipo", array('id_equipo' => $row['id_equipo']));
		$resultUsuarios = $queryUsuarios->fetchAll();
		
		
		$usuarios = array();
		foreach ($resultUsuarios as $rowU){
			$rowU = (array)$rowU;
			
			$query = $database->query("SELECT * FROM {tp_responsable} WHERE id_responsable = :id_responsable", [
				':id_responsable' => $rowU['id_responsable'],
			]);
			$resultResponsable = $query->fetchAssoc();
			
			$nombre_usuario = $resultResponsable['username'];
			
			array_push($usuarios, $nombre_usuario);
			$usuarioss = array_values(array_unique($usuarios));
		}
		
		$usuariosCadena = implode(",", $usuarioss);
		
		$array_equipo['usuarios'] = $usuariosCadena;
		$array_equipo['fecha_creacion'] = date('d/m/Y', (int)$row['fecha_creacion']);
		$array_equipo['fecha_edicion'] = date('d/m/Y', (int)$row['fecha_edicion']);
		$array_equipo['uid_creador'] = $row['uid_creador'];
		$array_equipo['uid_editor'] = $row['uid_editor'];
		$array_equipo['estado'] = $row['estado'];
		$array_equipo['editar'] = $row['editar'];
		$array_equipo['eliminar'] = $row['eliminar'];
		
		array_push($equipoArray, $array_equipo); // inserto evento en el array de calendario
	}

    return $equipoArray;

}
