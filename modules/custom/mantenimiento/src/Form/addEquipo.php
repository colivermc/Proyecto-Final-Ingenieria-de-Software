<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addEquipo extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'mantenimiento_addEquipo';
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
	
		$database = \Drupal::database();
		
		$query = $database->query("SELECT * FROM tp_proyecto WHERE estado = 1");
		$result = $query->fetchAll();
		
		$eventos = array();
		foreach ($result as $row){
			$row = (array) $row;
			$eventos[$row['id_evento']] = $row['evento'];
		}
	
		$form['datos_equipo']['equipo'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Nombre'),
		  '#size' => 100,
		  '#maxlength' => 128,
		  '#required' => TRUE,
		);
		
		$queryUsuarios = $database->query("SELECT * FROM tp_responsable WHERE estado = 1");
		$resultUsuarios = $queryUsuarios->fetchAll();
		
		$usuarios = array();
		foreach ($resultUsuarios as $rowU){
			$rowU = (array)$rowU;
			
			$usuarios[$rowU['id_responsable']] = $rowU['username'];
		}
		
		$form['datos_equipo']['user'] = array(
			'#type' => 'select',
			'#title' => $this->t('Usuarios'),
			'#options' => $usuarios,
			'#multiple' => TRUE,
			'#required' => TRUE,
		);
		
		
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
			'#submit' => array('mantenimiento_equipo_cancelar'),
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
	
	}
	
	/**
	* {@inheritdoc}
	*/
	
	
	
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
		$connection = \Drupal::database();

		$uid_user = \Drupal::currentUser()->id();

		$campos = array(
			'nombre' => trim($form_state->getValue('equipo')),
			'fecha_creacion' => time(),
			'uid_creador' => $uid_user,
		);

		$resultEquipo = $connection->insert('tp_equipo')
		->fields($campos)
		->execute();

		
		$user = $form_state->getValue('user');
		
		foreach ($user as $user){
			$camposUsers = array(
				'id_responsable' => (int)$user,
				'id_equipo' => $resultEquipo,
			);
			
			$result = $connection->insert('tp_user_equipo')
				->fields($camposUsers)
				->execute();
		}
		
		
		drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");

		$form_state->setRedirect('mantenimiento.equipos');
	
	}

}
