<?php



namespace Drupal\checklist\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editTarea extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checklist_editTarea';
  }

  public function Listarunregistro($id_proyecto, $id_tarea) {

    $database = \Drupal::database();


    $query = $database->query("SELECT tarea.id_tarea,
    tarea.codigo,
    tarea.nombre,
    tarea.descripcion,
    proyecto.proyecto,
    tarea.checkin,
    tarea.id_tipo_tarea,
    tarea.id_estado_tarea,
    tarea.prioridad,
    tarea.fecha_inicio,
    tarea.fecha_fin,
    tarea.fecha_creacion,
    tarea.fecha_edicion,
    tarea.uid_creador,
    tarea.uid_editor
    FROM tp_tarea as tarea
    INNER JOIN tp_proyecto as proyecto ON tarea.id_proyecto = proyecto.id_proyecto
    WHERE proyecto.id_proyecto = :id_proyecto AND tarea.id_tarea = :id_tarea", array('id_proyecto' => $id_proyecto, 'id_tarea' => $id_tarea));
    $result = $query->fetchAssoc();


    $usuario_creador = \Drupal\user\Entity\User::load($result['uid_creador']);

    if (!empty($result['uid_editor'])){
      $usuario_editor = \Drupal\user\Entity\User::load($result['uid_editor']);
      $result['uid_editor'] = $usuario_editor->get('name')->value;
    }

    $result['uid_creador'] = $usuario_creador->get('name')->value;

    return $result;

  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id_proyecto = null, $id_tarea = null) {
	  $form['#attached']['library'][] = 'checklist/tarea_library';

	  $database = \Drupal::database();

    $registro = array();
    $registro = $this->Listarunregistro($id_proyecto, $id_tarea);
    //ksm($this->Listarunregistro($arg));


      // ----------
	  $queryEP = $database->query("SELECT proyecto.proyecto, pais.nombre, pais.id_pais FROM tp_proyecto as proyecto
			INNER JOIN tp_pais as pais ON proyecto.id_pais = pais.id_pais
			WHERE proyecto.id_proyecto = :id_proyecto", array('id_proyecto' => $id_proyecto));
	  $resultEP = $queryEP->fetchAll();

	  $proyecto = "";
	  $pais = "";
	  $id_pais = 0;

	  foreach ($resultEP as $row){
		  $row = (array) $row;
		  $proyecto = $row['proyecto'];
		  $pais = $row['nombre'];
		  $id_pais = $row['id_pais'];
	  }
	
	  $form['titulo'] = array(
		  '#type' => 'markup',
		  '#markup' => '<h1>'.$proyecto.' </h1><h1>País: '.$pais.'</h1>'
	  );
	  // ----------


    // CAMPOS TAREA

    $form['tarea'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Tarea'),
      '#attributes' => array(
        'class' => array('tarea')
      ),
    );

    $form['tarea']['tarea_codigo'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Código'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $registro['codigo'],
    );

    $form['tarea']['tarea_nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $registro['nombre'],
    );

    $form['tarea']['tarea_descripcion'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Descripción'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $registro['descripcion'],
    );

//    $form['tarea']['tarea_checkin'] = array(
//      '#type' => 'checkbox',
//      '#title' => $this->t('Finalizada'),
//      '#return_value' => '1', // 1 = "tarea realizada"
//      '#size' => 60,
//      '#maxlength' => 128,
//      '#default_value' => $registro['checkin'],
//    );





      $queryTipoTarea = $database->query("SELECT * FROM tp_tipo_tarea WHERE estado = 1");
      $resultTiposTareas = $queryTipoTarea->fetchAll();

      $tipos_tareas = array();
      foreach ($resultTiposTareas as $rowTT){
          $rowTT = (array)$rowTT;

          $tipos_tareas[$rowTT['id_tipo_tarea']] = $rowTT['nombre'];
      }

      $form['tarea']['tipo_tarea'] = array(
          '#type' => 'select',
          '#title' => $this->t('Tipo de tarea'),
          '#options' => $tipos_tareas,
          '#required' => TRUE,
          '#default_value' => $registro['id_tipo_tarea'],
      );

      $queryEstadoTarea = $database->query("SELECT * FROM tp_estado_tarea WHERE estado = 1");
      $resultEstadoTarea = $queryEstadoTarea->fetchAll();

      $estados_tareas = array();
      foreach ($resultEstadoTarea as $rowET){
          $rowET = (array)$rowET;

          $estados_tareas[$rowET['id_estado_tarea']] = $rowET['nombre'];
      }

      $form['tarea']['estado_tarea'] = array(
          '#type' => 'select',
          '#title' => $this->t('Estado de la tarea'),
          '#options' => $estados_tareas,
          '#required' => TRUE,
          '#default_value' => $registro['id_estado_tarea'],
      );


      $prioridad = array();
      $prioridad['Baja'] = 'Baja';
      $prioridad['Media'] = 'Media';
      $prioridad['Alta'] = 'Alta';

      $form['tarea']['prioridad'] = array(
          '#type' => 'select',
          '#title' => $this->t('Prioridad'),
          '#options' => $prioridad,
          '#required' => TRUE,
          '#default_value' => $registro['prioridad'],
      );


      $form['tarea']['fecha_inicio'] = [
          '#type' => 'datetime',
          '#title' => 'Fecha Iniciación',
          '#required' => TRUE,
          '#description' => t('23/12/2019'),
          '#date_date_format' => 'd/m/Y',
          '#date_time_format' => 'H:m',
          '#default_value' => DrupalDateTime::createFromTimestamp((int)$registro['fecha_inicio']),
          '#prefix' => '<div class="field-datetime">',
          '#suffix' => '</div>',
      ];

      $form['tarea']['fecha_fin'] = [
          '#type' => 'datetime',
          '#title' => 'Fecha Finalización',
          '#required' => TRUE,
          '#description' => t('23/12/2019'),
          '#date_date_format' => 'd/m/Y',
          '#date_time_format' => 'H:m',
          '#default_value' => DrupalDateTime::createFromTimestamp((int)$registro['fecha_fin']),
          '#prefix' => '<div class="field-datetime">',
          '#suffix' => '</div>',
      ];



	  // EQUIPOS USUARIOS
	  $queryEquipos = $database->query("SELECT * FROM tp_equipo WHERE estado = 1");
	  $resultEquipos = $queryEquipos->fetchAll();

	  $usuarios = array();
	  foreach ($resultEquipos as $row){
		  $row = (array) $row;

		  $queryUsuarios = $database->query("SELECT *
			FROM tp_responsable as responsable
			INNER JOIN tp_user_equipo as uequipo ON responsable.id_responsable = uequipo.id_responsable
			WHERE uequipo.id_equipo = :id_equipo", array('id_equipo' => $row['id_equipo']));
		  $resultUsuarios = $queryUsuarios->fetchAll();

		  foreach ($resultUsuarios as $rowU){
			  $rowU = (array)$rowU;

			  $usuarios[$rowU['id_responsable']] = $rowU['username'];
		  }

		  // =====
		  $queryUsuariosTarea = $database->query("SELECT *
			FROM tp_responsable as responsable
			INNER JOIN tp_user_turno as uturno ON responsable.id_responsable = uturno.id_responsable
			WHERE uturno.id_equipo = :id_equipo and uturno.id_tarea = :id_tarea", array('id_equipo' => $row['id_equipo'], 'id_tarea' => $id_tarea));
		  $resultUsuariosTarea = $queryUsuariosTarea->fetchAll();

		  $usuariosSelected = array();
		  foreach ($resultUsuariosTarea as $rowUT){
			  $rowUT = (array)$rowUT;

			  array_push($usuariosSelected, $rowUT['id_responsable']);
		  }

		  $form['datos_equipo']['equipo'.$row['id_equipo']] = array(
			  '#type' => 'select',
			  '#title' => $this->t('Equipo '.$row['nombre']),
			  '#options' => $usuarios,
//			  '#required' => TRUE,
			  '#multiple' => TRUE,
			  '#default_value' => $usuariosSelected,
		  );
		  unset($usuarios);
	  }
	  // ----------------

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#attributes' => array(
        'class' => array('guardar_registro')
      ),
    );

    $form['actions']['cancelar'] = array(
		'#type' => 'submit',
		'#value' => $this->t('Cancel'),
		'#submit' => array('::checklist_tarea_cancelar'),
		'#limit_validation_errors' => array(),
		'#id_proyecto' => $id_proyecto,
		'#attributes' => array(
		'class' => array('cancelar_registro')
		),
    );

    $form['id_proyecto'] = array(
      '#type' => 'hidden',
      '#value' => $id_proyecto
    );

    $form['id_tarea'] = array(
      '#type' => 'hidden',
      '#value' => $id_tarea
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    }


	public function submitForm(array &$form, FormStateInterface $form_state) {

		$uid_user = \Drupal::currentUser()->id();

		$connection = \Drupal::database();

        $formulario = $form_state->getUserInput();



        $campos_tarea = array(
            'codigo' => trim($form_state->getValue('tarea_codigo')),
            'nombre' => trim($form_state->getValue('tarea_nombre')),
            'descripcion' => trim($form_state->getValue('tarea_descripcion')),
            'id_proyecto' => $form_state->getValue('id_proyecto'),
//				'checkin' => $form_state->getValue('tarea_checkin'),
            'id_tipo_tarea' => $form_state->getValue('tipo_tarea'),
            'id_estado_tarea' => $form_state->getValue('estado_tarea'),
            'prioridad' => $form_state->getValue('prioridad'),
            'fecha_inicio' => strtotime($form_state->getValue('fecha_inicio')),
            'fecha_fin' => strtotime($form_state->getValue('fecha_fin')),
            'fecha_edicion' => time(),
            'uid_editor' => $uid_user,
        );

        $resulTarea = $connection->update('tp_tarea')
            ->fields($campos_tarea)
            ->condition('id_proyecto', $form_state->getValue('id_proyecto'))
            ->condition('id_tarea', $form_state->getValue('id_tarea'))
            ->execute();



        // GUARDADO DE USUARIOS ENCARGADOS

        $queryEquipos = $connection->query("SELECT * FROM tp_equipo WHERE estado = 1");
        $resultEquipos = $queryEquipos->fetchAll();

        $deleteUserTurno = $connection->delete('tp_user_turno')
            ->condition('id_tarea', $form_state->getValue('id_tarea'), '=')
            ->execute();

        foreach ($resultEquipos as $row){
              $row = (array) $row;

              $usuarios = $form_state->getValue('equipo'.$row['id_equipo']);
              $usuarios = (array)$usuarios;

              foreach ($usuarios as $u){
            //				$u = (array) $u;

                  $campos_usuarios = array(
                      'id_equipo' => $row['id_equipo'],
                      'id_responsable' => $u,
                      'id_tarea' => $form_state->getValue('id_tarea'),
                  );

                  $insertUturno = $connection->insert('tp_user_turno')
                      ->fields($campos_usuarios)
                      ->execute();
              }
        }


		drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $form_state->getValue('tarea_nombre'));

		$form_state->setRedirect('checklist.tareas', array('id_proyecto' => $form_state->getValue('id_proyecto')));

	}

	public function checklist_tarea_cancelar (array &$form, FormStateInterface $form_state) {

		drupal_set_message('Acción Cancelada!','error');

		$id_proyecto  = $form_state->getTriggeringElement()['#id_proyecto'];

		global $base_url;

		$response = new RedirectResponse($base_url.'/proyecto/'.$id_proyecto.'/tareas');
		$response->send();
		return;

	}

}
