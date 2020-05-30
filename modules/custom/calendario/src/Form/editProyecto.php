<?php



namespace Drupal\calendario\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editProyecto extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendario_editProyecto';
  }

  public function Listarunregistro($arg) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT * FROM {tp_proyecto} WHERE id_proyecto = :id_proyecto", [
      ':id_proyecto' => $arg,
    ]);
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
	public function buildForm(array $form, FormStateInterface $form_state, $arg=null) {
	
		$registro = array();
		$registro = $this->Listarunregistro($arg);

		$fecha_inicio = date('Y-m-d', $registro['fecha_inicio']);
		$fecha_finalizacion = date('Y-m-d', $registro['fecha_finalizacion']);

		$form['datos_proyecto']['fecha_inicio'] = array(
			'#type' => 'datetime',
			'#title' => 'Fecha Transmisión',
			'#required' => TRUE,
	        '#default_value' => DrupalDateTime::createFromTimestamp((int)$registro['fecha_inicio']),
			'#format' => 'd/m/Y H:i',
			'#description' => t('23/12/2019'),
			'#prefix' => '<div class="field-datetime">',
			'#suffix' => '</div>',
		);

		$form['datos_proyecto']['duracion'] = array(
			'#type' => 'number',
			'#title' => 'Duración del proyecto (en Minutos)',
			'#required' => TRUE,
			'#description' => t('ejemplo: 100 minutos'),
			'#default_value' => $registro['duracion'],
		);

		$form['datos_proyecto']['fecha_finalizacion'] = array(
			'#type' => 'datetime',
			'#title' => 'Fecha Finalización',
			'#required' => TRUE,
			'#default_value' => DrupalDateTime::createFromTimestamp((int)$registro['fecha_finalizacion']),
			'#format' => 'd/m/Y H:i',
			'#description' => t('23/12/2019'),
			'#prefix' => '<div class="field-datetime">',
			'#suffix' => '</div>',
		);

		$form['datos_proyecto']['proyecto'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Nombre del proyecto'),
			//'#default_value' => $node->title,
			'#size' => 100,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['proyecto'],
		);

		$database = \Drupal::database();

		$queryPais = $database->query("SELECT * FROM tp_pais WHERE estado = 1");
		$resultPais = $queryPais->fetchAll();

		$paices = array();
		foreach ($resultPais as $row){
			$row = (array) $row;
			$paices[$row['id_pais']] = $row['nombre'];
		}

		$form['datos_proyecto']['pais'] = array(
			'#type' => 'select',
			'#title' => $this->t('Pais'),
			'#options' => $paices,
			'#required' => TRUE,
			'#default_value' => $registro['id_pais']
		);

        $form['descripcion_uturnos'] = array(
            '#markup' => '<p>Los responsables asignados seran los que se usen por defecto al no asignar responsables en la creación de tareas</p>'
        );
		
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
			INNER JOIN tp_user_turno_default as uturno ON responsable.id_responsable = uturno.id_responsable
			WHERE uturno.id_equipo = :id_equipo and uturno.id_proyecto = :id_proyecto", array('id_equipo' => $row['id_equipo'], 'id_proyecto' => $arg));
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
			'#submit' => array('calendario_proyecto_cancelar'),
			'#limit_validation_errors' => array(),
			'#attributes' => array(
				'class' => array('cancelar_accion')
			),
		);


		$form['id_proyecto'] = array(
		  '#type' => 'hidden',
		  '#value' => $arg
		);

		return $form;
	}

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  	if (!empty($form_state->getValue('fecha_finalizacion'))){
  		$fecha_inicio = strtotime($form_state->getValue('fecha_inicio'));
  		$fecha_finalizacion = strtotime($form_state->getValue('fecha_finalizacion'));
  		
  		if ($fecha_finalizacion < $fecha_inicio){
		    $form_state->setErrorByName('fecha_finalizacion', $this->t('La fecha ingresada es menos a la Fecha de transmisión del proyecto'));
	    }
  		
    }
  	
  	if (!empty($form_state->getValue('duracion'))){
  		$duracion = (int)$form_state->getValue('duracion');
  		
  		if ($duracion < 0){
		    $form_state->setErrorByName('duracion', $this->t('La duracion no puede ser negativa'));
	    }
    }
  	
  }

  /**
   * {@inheritdoc}
   */



	public function submitForm(array &$form, FormStateInterface $form_state) {
	
		$uid_user = \Drupal::currentUser()->id();
		
		$campos = array(
			'proyecto' => trim($form_state->getValue('proyecto')),
			'fecha_inicio' => strtotime($form_state->getValue('fecha_inicio')),
			'fecha_finalizacion' => strtotime($form_state->getValue('fecha_finalizacion')),
			'duracion' => trim($form_state->getValue('duracion')),
			'id_pais' => $form_state->getValue('pais'),
			'id_canal' =>$form_state->getValue('canal'),
			'estado_transmision' => 0, // 0 = no transmitido
			'id_empresa' => 1,
			'fecha_edicion' => time(),
			'uid_editor' => $uid_user
		);
		
		$connection = \Drupal::database();
		
		$result = $connection->update('tp_proyecto')
			->fields($campos)
			->condition('id_proyecto', $form_state->getValue('id_proyecto'), '=')
			->execute();
		
		
		// GUARDADO DE USUARIOS ENCARGADOS
		
		$queryEquipos = $connection->query("SELECT * FROM tp_equipo WHERE estado = 1");
		$resultEquipos = $queryEquipos->fetchAll();
		
		$deleteUserTurno = $connection->delete('tp_user_turno_default')
			->condition('id_proyecto', $form_state->getValue('id_proyecto'), '=')
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
					'id_proyecto' => $form_state->getValue('id_proyecto'),
				);
				
				$insertUturno = $connection->insert('tp_user_turno_default')
					->fields($campos_usuarios)
					->execute();
			}
		}
		
		drupal_set_message("Datos guardados correctamente. Se ha actualizado el proyecto \"". $form_state->getValue('proyecto')."\"");
		
		$form_state->setRedirect('calendario.proyecto');
	
	}

}
