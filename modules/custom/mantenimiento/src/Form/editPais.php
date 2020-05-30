<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editPais extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_editPais';
  }

  public function Listarunregistro($arg) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT * FROM {tp_pais} WHERE id_pais = :id_pais", [
      ':id_pais' => $arg,
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

    $form['datos_pais'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Datos País'),
      '#attributes' => array(
        'class' => array('datos_pais')
      ),
    );

    $form['datos_pais']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#default_value' => $registro['nombre'],
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_pais']['identificador'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Identificador'),
      '#default_value' => $registro['identificador'],
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
		
		$nombre = $form_state->getValue('nombre');
		$identificador = $form_state->getValue('identificador');
		$id_pais = $form_state->getValue('idregistro');
	
		$connection = \Drupal::database();
		
		if (!empty($form_state->getValue('nombre'))) {
			$queryNombre = $connection->select('tp_pais', 'pais')
				->fields('pais')
				->condition('nombre', '%'.$nombre.'%', 'LIKE')
				->condition('id_pais', $id_pais, '!=');
			$countNombre = $queryNombre->execute()->fetchAll();
			
			if (count($countNombre) > 0){
				$form_state->setErrorByName('nombre', $this->t('El nombre ingresado ya existe'));
			}
		}
		
		if (!empty($form_state->getValue('identificador'))) {
			$queryIdentificador = $connection->select('tp_pais', 'pais')
				->fields('pais')
				->condition('identificador', '%'.$identificador.'%', 'LIKE')
				->condition('id_pais', $id_pais, '!=');
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
      'fecha_edicion' => time(),
      'uid_editor' => $uid_user,
    );

    $id = $form_state->getValue('idregistro');


    $connection = \Drupal::database();

    $connection->update('tp_pais')
    ->fields($campos)
    ->condition('id_pais', $id)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $id );

    $form_state->setRedirect('mantenimiento.paises');
    
  }

}
