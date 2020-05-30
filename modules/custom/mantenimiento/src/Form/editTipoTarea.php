<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editTipoTarea extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_editTipoTarea';
  }

  public function Listarunregistro($arg) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT * FROM {tp_tipo_tarea} WHERE id_tipo_tarea = :id_tipo_tarea", [
      ':id_tipo_tarea' => $arg,
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

        $form['nombre'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Nombre'),
            '#default_value' => $registro['nombre'],
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
            '#submit' => array('mantenimiento_empresa_cancelar'),
            '#limit_validation_errors' => array(),
            '#attributes' => array(
            'class' => array('cancelar_registro')
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
            'fecha_edicion' => time(),
            'uid_editor' => $uid_user,
        );

        $id = $form_state->getValue('idregistro');


        $connection = \Drupal::database();

        $connection->update('tp_tipo_tarea')
        ->fields($campos)
        ->condition('id_tipo_tarea', $id)
        ->execute();

        drupal_set_message("Datos guardados correctamente. Se ha actualizado el tipo de tarea ". trim($form_state->getValue('nombre')) );

        $form_state->setRedirect('mantenimiento.tipos_tarea');

    }

}
