<?php



namespace Drupal\calendario\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Datetime\DrupalDateTime;
/**
 * Implements an example form.
 */
class addProyecto extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
	return 'calendario_addProyecto';
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
	
		$form['datos_proyecto']['fecha_inicio'] = array(
		  '#type' => 'datetime',
		  '#title' => 'Fecha Iniciación',
		  '#required' => TRUE,
		//      '#default_value' => array('month' => 9, 'day' => 6, 'year' => 1962),
		  '#format' => 'd/m/Y H:i',
		  '#description' => t('23/12/2019'),
			'#prefix' => '<div class="field-datetime">',
			'#suffix' => '</div>',
		);
		
		//    $form['datos_proyecto']['duracion'] = array(
		//      '#type' => 'datetime',
		//      '#title' => 'Duración del proyecto',
		//      '#required' => TRUE,
		//      '#description' => t('07:30:00'),
		//      '#date_date_element' => 'none',
		//      '#date_time_element' => 'time',
		//      '#date_time_format' => 'H:i'
		//    );
	
		$form['datos_proyecto']['duracion'] = array(
		  '#type' => 'number',
		  '#title' => 'Tiempo estimado (en Minutos)',
		  '#required' => TRUE,
		  '#description' => t('ejemplo: 100 minutos'),
		);
	
		$form['datos_proyecto']['fecha_finalizacion'] = array(
		  '#type' => 'datetime',
		  '#title' => 'Fecha Finalización',
		  '#required' => TRUE,
		  '#format' => 'd/m/Y H:i',
		  '#description' => t('23/12/2019'),
			'#prefix' => '<div class="field-datetime">',
			'#suffix' => '</div>',
		);
	
		$form['datos_proyecto']['proyecto'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Nombre del Proyecto'),
		  //'#default_value' => $node->title,
		  '#size' => 100,
		  '#maxlength' => 128,
		  '#required' => TRUE,
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
		);
	
		$form['descripcion_uturnos'] = array(
			'#markup' => '<p>Los responsables asignados seran los que se usen por defecto al no asignar responsables en la creación de tareas</p>'
		);
	
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
            'duracion' => trim($form_state->getValue('duracion')),
	        'fecha_finalizacion' => strtotime($form_state->getValue('fecha_finalizacion')),
            'id_pais' => $form_state->getValue('pais'),
            'id_canal' => 1, //pongo esto por ahorita
            'estado_transmision' => 0, // 0 = no transmitido
            'id_empresa' => 1,
            'fecha_creacion' => time(),
            'uid_creador' => $uid_user
        );

        $connection = \Drupal::database();

        $result = $connection->insert('tp_proyecto')
        ->fields($campos)
        ->execute();
	
	    
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
				    'id_proyecto' => $result,
			    );
			
			    $insertUturno = $connection->insert('tp_user_turno_default')
				    ->fields($campos_usuarios)
				    ->execute();
		    }
	    }
	    
	    $nombre_proyecto = trim($form_state->getValue('proyecto'));

        drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $nombre_proyecto."\"");

        $form_state->setRedirect('calendario.proyecto');


    }

}
