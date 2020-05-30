<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editCanalSlack extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_editCanalSlack';
  }

  public function Listarunregistro($arg) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT * FROM {tp_canal_slack} WHERE id_canal_slack = :id_canal_slack", [
      ':id_canal_slack' => $arg,
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

    $form['datos_canal_slack'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Datos del canal'),
      '#attributes' => array(
        'class' => array('datos_canal_slack')
      ),
    );

    $form['datos_canal_slack']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#default_value' => $registro['nombre'],
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
    $form['datos_canal_slack']['pais'] = array(
      '#type' => 'select',
      '#title' => $this->t('Pais'),
      '#options' => $paises,
      '#default_value' => $registro['pais'],
      '#required' => TRUE,
    );

    $form['datos_canal_slack']['hook'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Hook'),
      '#size' => 100,
      '#maxlength' => 128,
      '#default_value' => $registro['hook_canal_slack'],
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
      'nombre' => trim($form_state->getValue('nombre')),
      'pais' => trim($form_state->getValue('pais')),
      'hook_canal_slack' => trim($form_state->getValue('hook')),
      'fecha_edicion' => time(),
      'uid_editor' => $uid_user,
    );

    $id = $form_state->getValue('idregistro');

    $connection = \Drupal::database();

    $connection->update('tp_canal_slack')
    ->fields($campos)
    ->condition('id_canal_slack', $id)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $id );

    $form_state->setRedirect('mantenimiento.canalesslack');
  }

}
