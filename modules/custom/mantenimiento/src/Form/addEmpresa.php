<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addEmpresa extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_addEmpresa';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

//    $form['#attached']['library'][] = 'mantenimiento/form_example_libraries';
//    $form['#attached']['library'][] = 'seven/global-styling';

    $form['datos_empresa'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Datos Empresa'),
      '#attributes' => array(
        'class' => array('mi_clase')
      ),
    );

    $form['datos_empresa']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );
	
	  $form['datos_empresa']['descripcion'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Descripción'),
		  //'#default_value' => $node->title,
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
        'class' => array('mibotonprincipal')
      ),
    );

    $form['actions']['cancelar'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('mantenimiento_empresa_cancelar'),
      '#limit_validation_errors' => array(),
      '#attributes' => array(
        'class' => array('mibotonprincipal')
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
//    if (strlen($form_state->getValue('phone_number')) < 3) {
//      $form_state->setErrorByName('phone_number', $this->t('Este número telefónico es muy corto, por favor digite su número telefónico completo.'));
//    }
//
//
//    $mystring = $form_state->getValue('email');
//    $findme   = '@';
//    $pos = strpos($mystring, $findme);
//
//    // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
//    // porque la posición de 'a' está en el 1° (primer) caracter.
//    if ($pos === false) {
//      $form_state->setErrorByName('email', $this->t('Email no válido'));
//
//    }

  }

  /**
   * {@inheritdoc}
   */



  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid_user = \Drupal::currentUser()->id();

    $campos = array(
      'nombre' => trim($form_state->getValue('nombre')),
      'descripcion' => trim($form_state->getValue('descripcion')),
      'fecha_creacion' => time(),
      'uid_creador' => $uid_user,
    );

    $connection = \Drupal::database();

    $result = $connection->insert('tp_empresa')
    ->fields($campos)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");

    $form_state->setRedirect('mantenimiento.empresas');
    
  }

}
