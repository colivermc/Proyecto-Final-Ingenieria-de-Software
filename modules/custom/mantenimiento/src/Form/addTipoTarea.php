<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addTipoTarea extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_addTipoTarea';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
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
      '#submit' => array('mantenimiento_tipo_tarea_cancelar'),
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
      'fecha_creacion' => time(),
      'uid_creador' => $uid_user,
    );

    $connection = \Drupal::database();

    $result = $connection->insert('tp_tipo_tarea')
    ->fields($campos)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha creado el tipo de tarea \"".trim($form_state->getValue('nombre'))."\"");

    $form_state->setRedirect('mantenimiento.tipos_tarea');
    
  }

}
