<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addCanal extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'mantenimiento_addCanal';
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
	
		$form['aws_canal']['canal'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Identificador'),
			'#size' => 60,
			'#maxlength' => 100,
			'#required' => TRUE,
		);
		
		$form['aws_canal']['descripcion'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Descripsión'),
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
		$form['aws_canal']['pais'] = array(
			'#type' => 'select',
			'#title' => $this->t('Pais'),
			'#options' => $paises,
			'#required' => TRUE,
		);
		
		$form['aws_canal']['aws_region'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Region'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
		);
		
		$form['aws_canal']['aws_id'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Id'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
		);
		
		$form['aws_canal']['aws_name'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Name'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
		);
		
		$form['aws_canal']['aws_country'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('AWS Country'),
			'#size' => 60,
			'#maxlength' => 128,
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
			'#submit' => array('mantenimiento_canal_cancelar'),
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
	
		$uid_user = \Drupal::currentUser()->id();
		$id_pais = $form_state->getValue('pais');
		
		$pais = getRegistro('tp_pais', 'id_pais', $id_pais);
		
		$campos = array(
			'canal' => trim($form_state->getValue('canal')),
			'descripcion' => trim($form_state->getValue('descripcion')),
			'id_pais' => $form_state->getValue('pais'),
			'aws_region' => trim($form_state->getValue('aws_region')),
			'aws_id' => trim($form_state->getValue('aws_id')),
			'aws_name' => trim($form_state->getValue('aws_name')),
			'aws_country' => trim($form_state->getValue('aws_country')),
			'fecha_creacion' => time(),
			'uid_creador' => $uid_user,
		);
		
		$connection = \Drupal::database();
		
		$result = $connection->insert('tp_canal')
		->fields($campos)
		->execute();
		
		drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");
		
		$form_state->setRedirect('mantenimiento.canales');
	
	}

}
