<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class addCanalSlack extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_addCanalSlack';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#size' => 60,
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
    $form['pais'] = array(
      '#type' => 'select',
      '#title' => $this->t('Pais'),
      '#options' => $paises,
      '#required' => TRUE,
    );

    $form['hook'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Hook'),
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
      '#submit' => array('mantenimiento_canal_slack_cancelar'),
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
      'pais' => trim($form_state->getValue('pais')),
      'hook_canal_slack' => trim($form_state->getValue('hook')),
      'fecha_creacion' => time(),
      'uid_creador' => $uid_user,
    );

    $connection = \Drupal::database();

    $result = $connection->insert('tp_canal_slack')
    ->fields($campos)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha creado el registro \"". $result."\"");

    $form_state->setRedirect('mantenimiento.canalesslack');

  }

}
