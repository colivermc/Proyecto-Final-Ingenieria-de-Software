<?php

namespace Drupal\mantenimiento\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Url;
use Drupal\Core\Link;
//use Drupal\mantenimiento\Form\editPais;
use http\Client\Curl\User;


class paisController extends ControllerBase {

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


  public function mostrarPaises() {

    $contenido = array();

    $url = Url::fromRoute('mantenimiento.addpais');
    $project_link = Link::fromTextAndUrl(t('Agregar País'), $url);
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
    $rows = listar_paises();
    //ksm(listar());
    // Build a render array which will be themed as a table with a pager.
     $contenido['table'] = [
       '#rows' => $rows,
       '#header' => [t('Id Pais'),
	       t('Nombre'),
	       t('Identificador'),
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


function listar_paises() {
    $database = \Drupal::database();
    // Using the TableSort Extender is what tells  the query object that we
    // are sorting.
    $query = $database->select('tp_pais', 'pais')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $query->fields('pais');
    $query->condition('pais.estado', '1', '=');

    // Don't forget to tell the query object how to find the header information.
    $result = $query
    //->orderByHeader($header)
    ->execute();

    $rows = [];

    global $base_url;
    foreach ($result as $row) {
        // Normally we would add some nice formatting to our rows
        // but for our purpose we are simply going to add our row
        // to the array.

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

        // External Uri.
        //use Drupal\Core\Url;
//        $url = Url::fromUri($base_url.'/pais/'.(int)$row['id_pais']);
//        $ver_link = \Drupal::l(t('Ver'), $url);
//        $row['ver']=$ver_link;

        $url = Url::fromUri($base_url.'/pais/'.(int)$row['id_pais'].'/edit', array('attributes' => [
	        'class' => ['use-ajax', 'editar'],
	        'data-dialog-type' => 'modal',
	        'data-dialog-options' => Json::encode([
		        'width' => 'auto',
		        'height' => 'auto'
	        ]),
        ]));
        $editar_link = \Drupal::l(t('Editar'), $url);
        $row['editar']=$editar_link;

        $url = Url::fromUri($base_url.'/pais/'.(int)$row['id_pais'].'/delete', array('attributes' => [
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
