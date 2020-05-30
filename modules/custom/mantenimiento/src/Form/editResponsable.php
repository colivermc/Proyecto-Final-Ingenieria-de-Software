<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editResponsable extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'mantenimiento_editResponsable';
	}
	
	public function Listarunregistro($arg) {
		$connection = \Drupal::database();
		$query = $connection->query("SELECT * FROM {tp_responsable} WHERE id_responsable = :id_responsable", [
		    ':id_responsable' => $arg,
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
		
		$form['datos_responsable']['nombre'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Nombre'),
			//'#default_value' => $node->title,
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['nombre']
		);
		
		$form['datos_responsable']['apellidos'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Apellidos'),
			//'#default_value' => $node->title,
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['apellido']
		);
		
		$form['datos_responsable']['username'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Nombre de Usuario'),
			//'#default_value' => $node->title,
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['username']
		);
		
		$form['datos_responsable']['direccion'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Dirección'),
			//'#default_value' => $node->title,
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['direccion']
		);
		
		$form['datos_responsable']['correo'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Correo Electrónico'),
			//'#default_value' => $node->title,
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['correo']
		);
		
		$form['datos_responsable']['telefono'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Teléfono'),
			//'#default_value' => $node->title,
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['telefono']
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
		  '#submit' => array('mantenimiento_responsable_cancelar'),
		  '#limit_validation_errors' => array(),
		  '#attributes' => array(
		    'class' => array('cancelar_registro')
		  ),
		);
		
		$form['idregistro'] = array(
		  '#type' => 'hidden',
		  '#value' => $arg
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
		
		$campos = array(
			'nombre' => trim($form_state->getValue('nombre')),
			'apellido' => trim($form_state->getValue('apellidos')),
			'username' => trim($form_state->getValue('username')),
			'direccion' => trim($form_state->getValue('direccion')),
			'correo' => trim($form_state->getValue('correo')),
			'telefono' => trim($form_state->getValue('telefono')),
			'fecha_edicion' => time(),
			'uid_editor' => $uid_user,
		);
		
		$id = $form_state->getValue('idregistro');
		
		
		$connection = \Drupal::database();
		
		$connection->update('tp_responsable')
		->fields($campos)
		->condition('id_responsable', $id)
		->execute();
		
		drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $form_state->getValue('username') );
		
		$form_state->setRedirect('mantenimiento.responsables');
	
	}

}
