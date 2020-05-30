<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addTipoEvento extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_addTipoEvento';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['datos_tipoEvento']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['datos_tipoEvento']['descripcion'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Descripsión'),
      '#size' => 100,
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

    $campos = array(
      'nombre' => trim($form_state->getValue('nombre')),
      'descripcion' => trim($form_state->getValue('descripcion')),
      'fecha_creacion' => time(),
      'uid_creador' => $uid_user,
    );

    $connection = \Drupal::database();

    $result = $connection->insert('tp_tipo_evento')
    ->fields($campos)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");

    $form_state->setRedirect('mantenimiento.tiposEvento');

  }

}
