<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editProyectoJira extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'mantenimiento_editProyectoJira';
	}

	public function Listarunregistro($arg) {
		$connection = \Drupal::database();
		$query = $connection->query("SELECT * FROM {tp_proyecto_jira} WHERE id_proyecto_jira = :id_proyecto_jira", [
		  ':id_proyecto_jira' => $arg,
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

		//    $form['#attached']['library'][] = 'mantenimiento/form_example_libraries';
		//    $form['#attached']['library'][] = 'seven/global-styling';

		$form['datos_jira'] = array(
			'#type' => 'fieldset',
			'#title' => $this->t('Datos del canal'),
			'#attributes' => array(
				'class' => array('datos_canal')
			),
		);

    $form['datos_jira']['proyecto'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Proyecto'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $registro['nombre']
    );

    $form['datos_jira']['key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Key'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $registro['key']
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
			'nombre' => trim($form_state->getValue('proyecto')),
			'key' => trim($form_state->getValue('key')),
			'fecha_edicion' => time(),
			'uid_editor' => $uid_user,
		);

		$id = $form_state->getValue('idregistro');


		$connection = \Drupal::database();

		$connection->update('tp_proyecto_jira')
		->fields($campos)
		->condition('id_proyecto_jira', $id)
		->execute();

		drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $form_state->getValue('proyecto'));

		$form_state->setRedirect('mantenimiento.proyectosJira');

	}

}
