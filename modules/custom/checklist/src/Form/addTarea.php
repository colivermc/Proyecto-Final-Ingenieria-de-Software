<?php



namespace Drupal\checklist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\mantenimiento\Controller\tipoproyectoController;
use Drupal\user\Entity\User;
use http\Message;
use phpDocumentor\Reflection\Type;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
/**
 * Implements an example form.
 */
class addTarea extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'checklist_addTarea';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $id_proyecto = null) {

		  $form['#attached']['library'][] = 'checklist/tarea_library';

		  $database = \Drupal::database();

		  $queryEP = $database->query("SELECT proyecto.proyecto, pais.nombre, pais.id_pais as pais FROM tp_proyecto as proyecto
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
			  $id_pais = $row['pais'];
		  }

		  $form['titulo'] = array(
		        '#type' => 'markup',
			    '#markup' => '<h1>'.$proyecto.' </h1><h1>País: '.$pais.'</h1>'
		  );

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
		);

		$form['tarea']['tarea_nombre'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Nombre'),
		  //'#default_value' => $node->title,
		  '#size' => 60,
		  '#maxlength' => 128,
		  '#required' => TRUE,
		);

		$form['tarea']['tarea_descripcion'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Descripción'),
		  '#size' => 60,
		  '#maxlength' => 128,
		  '#required' => TRUE,
		);

//		$form['tarea']['tarea_checkin'] = array(
//		  '#type' => 'checkbox',
//		  '#title' => $this->t('Finalizada'),
//		  '#return_value' => '1', // 1 = "tarea realizada"
//		  '#size' => 60,
//		  '#maxlength' => 128,
//		);



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
        );


        $form['tarea']['fecha_inicio'] = [
            '#type' => 'datetime',
            '#title' => 'Fecha Iniciación',
            '#required' => TRUE,
            '#description' => t('23/12/2019'),
            '#date_date_format' => 'd/m/Y',
            '#date_time_format' => 'H:m',
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
            '#prefix' => '<div class="field-datetime">',
            '#suffix' => '</div>',
        ];


		// TURNOS USUARIOS
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

			$form['datos_equipo']['equipo'.$row['id_equipo']] = array(
				'#type' => 'select',
				'#title' => $this->t('Equipo '.$row['nombre']),
				'#options' => $usuarios,
//				'#required' => TRUE,
				'#multiple' => TRUE,
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
			'class' => array('mibotonprincipal')
			),
		);

		$form['actions']['cancelar'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Cancel'),
			'#submit' => array('::checklist_tarea_cancelar'),
			'#id_proyecto' => $id_proyecto,
			'#limit_validation_errors' => array(),
			'#attributes' => array(
			'class' => array('mibotonprincipal')
			),
		);

		$form['id_proyecto'] = array(
			'#type' => 'hidden',
			'#value' => $id_proyecto
		);

		return $form;
	}



	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {

	}

	/**
	* {@inheritdoc}
	*/



	public function submitForm(array &$form, FormStateInterface $form_state) {

		$uid_user = \Drupal::currentUser()->id();

		$connection = \Drupal::database();
		
		$id_proyecto = $form_state->getValue('id_proyecto');

		$formulario = $form_state->getUserInput();



		

		$campos_tarea = array(
			'codigo' => trim($form_state->getValue('tarea_codigo')),
			'nombre' => trim($form_state->getValue('tarea_nombre')),
			'descripcion' => trim($form_state->getValue('tarea_descripcion')),
			'id_proyecto' => $form_state->getValue('id_proyecto'),
			'id_tipo_tarea' => $form_state->getValue('tipo_tarea'),
			'id_estado_tarea' => $form_state->getValue('estado_tarea'),
            'prioridad' => $form_state->getValue('prioridad'),
            'fecha_inicio' => strtotime($form_state->getValue('fecha_inicio')),
            'fecha_fin' => strtotime($form_state->getValue('fecha_fin')),
			'checkin' => 0,
			'fecha_creacion' => time(),
			'uid_creador' => $uid_user,
		);

		$resulTarea = $connection->insert('tp_tarea')
		  ->fields($campos_tarea)
		  ->execute();

		
		// verificando si se asignaron responsables a la tarea
		$verificador = 0;
		$queryEquipos = $connection->query("SELECT * FROM tp_equipo WHERE estado = 1");
		$resultEquipos = $queryEquipos->fetchAll();
		
		foreach ($resultEquipos as $row) {
			$row = (array)$row;
			
			$usuarios = $form_state->getValue('equipo' . $row['id_equipo']);
			
			if (count($usuarios) > 0){
				$verificador = $verificador + 1;
			}
			
		}

		$usuarios_notificacion = array();
		
		if ($verificador > 0){
			// GUARDADO DE USUARIOS ENCARGADOS
			
			$queryEquipos = $connection->query("SELECT * FROM tp_equipo WHERE estado = 1");
			$resultEquipos = $queryEquipos->fetchAll();
			
			foreach ($resultEquipos as $row){
				$row = (array) $row;
				
				$usuarios = $form_state->getValue('equipo'.$row['id_equipo']);
				$usuarios = (array)$usuarios;
				
				foreach ($usuarios as $u){
//				$u = (array) $u;
					
					$campos_usuarios = array(
						'id_equipo' => $row['id_equipo'],
						'id_responsable' => $u,
						'id_tarea' => $resulTarea,
					);

					array_push($usuarios_notificacion, $u);
					
					$insertUturno = $connection->insert('tp_user_turno')
						->fields($campos_usuarios)
						->execute();
				}
			}
		}else{
			// GUARDADO DE USUARIOS ENCARGADOS DEFAULT
			
			$queryEquipos = $connection->query("SELECT * FROM tp_equipo WHERE estado = 1");
			$resultEquipos = $queryEquipos->fetchAll();
			
			foreach ($resultEquipos as $row){
				$row = (array) $row;
				
				$queryResponsablesproyecto = $connection->select('tp_user_turno_default', 'uturno');
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

                    array_push($usuarios_notificacion, $u);
					
					$insertUturno = $connection->insert('tp_user_turno')
						->fields($campos_usuarios)
						->execute();
				}
			}
		}


		//  ------------------------------------------------------------------------------------------------------------
        //                                  NOTIFICACION DE ASIGNACION DE TAREA
        //  ------------------------------------------------------------------------------------------------------------

        // buscando el correo de los responsables
        $queryResponsables = $connection->select('tp_responsable', 'responsable');
        $queryResponsables->fields('responsable');
        $queryResponsables->condition('responsable.id_responsable', (int)$usuarios_notificacion, 'IN');
        $result_responsables = $queryResponsables->execute()->fetchAll();

        $email_responsables = array();
        foreach ($result_responsables as $r) {
            $r = (array)$r;

            array_push($email_responsables, $r['correo']);
        }

        $usuario_creador_u = \Drupal\user\Entity\User::load($uid_user);
        $usuario_creador_tarea = $usuario_creador_u->get('name')->value;

        // envio de correo a cada uno de los responsables

        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'checklist';
        $key = 'notificacion_asignacion_de_tarea';
        $to = 'carlosoliver921@gmail.com'/*implode(',', $email_responsables)*/;
        $params['message'] = $usuario_creador_tarea.' te asignó la siguiente tarea: 
        TAREA, "'.trim($form_state->getValue('tarea_nombre')).'"
        Fecha de inicio: '.date('d/m/Y H:i', strtotime($form_state->getValue('fecha_inicio'))).'
        Fecha de finalización: '.date('d/m/Y H:i', strtotime($form_state->getValue('fecha_fin')));
        $params['node_title'] = trim($form_state->getValue('tarea_nombre'));
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;

        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
        if ($result['result'] !== true) {
            \Drupal::logger('checklist')->error('Hubo un problema al notificar a los responsables de la tarea: '.trim($form_state->getValue('tarea_nombre')));
        }
        else {
            \Drupal::logger('checklist')->notice('Se notificó a los responsables de la tarea: '.trim($form_state->getValue('tarea_nombre')));
        }
        //  ------------------------------------------------------------------------------------------------------------
		

		
		drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $form_state->getValue('tarea_nombre')."\"");

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
