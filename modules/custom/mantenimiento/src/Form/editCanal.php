<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editCanal extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'mantenimiento_editCanal';
	}
	
	public function Listarunregistro($arg) {
		$connection = \Drupal::database();
		$query = $connection->query("SELECT * FROM {tp_canal} WHERE id_canal = :id_canal", [
		  ':id_canal' => $arg,
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
		//ksm($this->Listarunregistro($arg));
		
		//    $form['#attached']['library'][] = 'mantenimiento/form_example_libraries';
		//    $form['#attached']['library'][] = 'seven/global-styling';
		
		$form['datos_canal'] = array(
			'#type' => 'fieldset',
			'#title' => $this->t('Datos del canal'),
			'#attributes' => array(
				'class' => array('datos_canal')
			),
		);
		
		$form['datos_canal']['canal'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Identificador'),
			'#default_value' => $registro['canal'],
			'#size' => 60,
			'#maxlength' => 100,
			'#required' => TRUE,
		);
		
		$form['datos_canal']['descripcion'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Descripción'),
			'#default_value' => $registro['descripcion'],
			'#size' => 100,
			'#maxlength' => 128,
			'#required' => TRUE,
		);
		
		$database = \Drupal::database();
		$query = $database->select('tp_pais', 'pais');
		$query->fields('pais', array('id_pais', 'nombre'));
		$query->condition('pais.estado', '1', '=');
		$result = $query->execute()->fetchAll();
		
		$paises = [];
		foreach($result as $r){
		    $paises[$r->id_pais] = $r->nombre;
		}
		$form['datos_canal']['pais'] = array(
			'#type' => 'select',
			'#title' => $this->t('Pais'),
			'#options' => $paises,
			'#default_value' => $registro['id_pais'],
			'#required' => TRUE,
		);
		
		$form['aws_canal']['aws_region'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Region'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['aws_region'],
		);
		
		$form['aws_canal']['aws_id'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Id'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['aws_id'],
		);
		
		$form['aws_canal']['aws_name'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Name'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['aws_name'],
		);
		
		$form['aws_canal']['aws_country'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Country'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
			'#default_value' => $registro['aws_country'],
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
			'#submit' => array('mantenimiento_canal_cancelar'),
			'#limit_validation_errors' => array(),
			'#attributes' => array(
				'class' => array('cancelar_accion')
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
	
	/**
	* {@inheritdoc}
	*/
	
	
	
	public function submitForm(array &$form, FormStateInterface $form_state) {
	
		$uid_user = \Drupal::currentUser()->id();
		
		$campos = array(
			'canal' => trim($form_state->getValue('canal')),
			'descripcion' => trim($form_state->getValue('descripcion')),
			'id_pais' => $form_state->getValue('pais'),
			'aws_region' => trim($form_state->getValue('aws_region')),
			'aws_id' => trim($form_state->getValue('aws_id')),
			'aws_name' => trim($form_state->getValue('aws_name')),
			'aws_country' => trim($form_state->getValue('aws_country')),
			'fecha_edicion' => time(),
			'uid_editor' => $uid_user,
		);
		
		$id = $form_state->getValue('idregistro');
		
		
		$connection = \Drupal::database();
		
		$connection->update('tp_canal')
		->fields($campos)
		->condition('id_canal', $id)
		->execute();
		
		drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $id );
		
		$form_state->setRedirect('mantenimiento.canales');
	
	}

}
