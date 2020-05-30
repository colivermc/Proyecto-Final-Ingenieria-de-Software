<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addResponsable extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_addResponsable';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

//    $form['#attached']['library'][] = 'mantenimiento/form_example_libraries';
//    $form['#attached']['library'][] = 'seven/global-styling';

    $form['datos_responsable'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Datos Responsable'),
      '#attributes' => array(
        'class' => array('mi_clase')
      ),
    );

    $form['datos_responsable']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_responsable']['apellidos'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Apellidos'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_responsable']['username'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre de Usuario'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_responsable']['direccion'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Dirección'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_responsable']['correo'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Correo Electrónico'),
      //'#default_value' => $node->title,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_responsable']['telefono'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Teléfono'),
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
      '#submit' => array('mantenimiento_responsable_cancelar'),
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

  }

  /**
   * {@inheritdoc}
   */



  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid_user = \Drupal::currentUser()->id();

    $campos = array(
      'nombre' => trim($form_state->getValue('nombre')),
      'apellido' => trim($form_state->getValue('apellidos')),
      'username' => trim($form_state->getValue('username')),
      'direccion' => trim($form_state->getValue('direccion')),
      'correo' => trim($form_state->getValue('correo')),
      'telefono' => trim($form_state->getValue('telefono')),
      'fecha_creacion' => time(),
      'uid_creador' => $uid_user,
    );

    $connection = \Drupal::database();

    $result = $connection->insert('tp_responsable')
    ->fields($campos)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");

    $form_state->setRedirect('mantenimiento.responsables');

  }

}
