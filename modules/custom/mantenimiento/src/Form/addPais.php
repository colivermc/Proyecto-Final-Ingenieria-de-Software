<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addPais extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_addPais';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['datos_pais'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Datos Pais'),
      '#attributes' => array(
        'class' => array('datos_pais')
      ),
    );

    $form['datos_pais']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_pais']['identificador'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Identificador'),
      '#description' => 'Identificador de país, ej. GT, SV, CR',
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
      '#submit' => array('mantenimiento_pais_cancelar'),
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
	
	  $nombre = $form_state->getValue('nombre');
	  $identificador = $form_state->getValue('identificador');
	
	  $connection = \Drupal::database();
	
	  if (!empty($form_state->getValue('nombre'))) {
		  $queryNombre = $connection->select('tp_pais', 'pais')
			  ->fields('pais')
			  ->condition('nombre', '%'.$nombre.'%', 'LIKE');
		  $countNombre = $queryNombre->execute()->fetchAll();
		
		  if (count($countNombre) > 0){
			  $form_state->setErrorByName('nombre', $this->t('El nombre ingresado ya existe'));
		  }
	  }
	
	  if (!empty($form_state->getValue('identificador'))) {
		  $queryIdentificador = $connection->select('tp_pais', 'pais')
			  ->fields('pais')
			  ->condition('identificador', '%'.$identificador.'%', 'LIKE');
		  $countIdentificador = $queryIdentificador->execute()->fetchAll();
		
		  if (count($countIdentificador) > 0){
			  $form_state->setErrorByName('identificador', $this->t('El identificador ingresado ya existe'));
		  }
	  }
	
	  parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */



  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid_user = \Drupal::currentUser()->id();

    $campos = array(
      'nombre' => trim($form_state->getValue('nombre')),
      'identificador' => strtoupper(trim($form_state->getValue('identificador'))),
      'fecha_creacion' => time(),
      'uid_creador' => $uid_user,
    );

    $connection = \Drupal::database();

    $result = $connection->insert('tp_pais')
    ->fields($campos)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");

    $form_state->setRedirect('mantenimiento.paises');

  }

}
