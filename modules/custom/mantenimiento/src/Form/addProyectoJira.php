<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addProyectoJira extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'mantenimiento_addProyectoJira';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {

		$form['jira']['proyecto'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Proyecto'),
			'#size' => 60,
			'#maxlength' => 128,
			'#required' => TRUE,
		);

    $form['jira']['key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
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
			'#submit' => array('mantenimiento_proyecto_jira_cancelar'),
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

		$campos = array(
			'nombre' => trim($form_state->getValue('proyecto')),
			'key' => trim($form_state->getValue('key')),
			'fecha_creacion' => time(),
			'uid_creador' => $uid_user,
		);

		$connection = \Drupal::database();

		$result = $connection->insert('tp_proyecto_jira')
		->fields($campos)
		->execute();

		drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $form_state->getValue('proyecto')."\"");

		$form_state->setRedirect('mantenimiento.proyectosJira');

	}

}
