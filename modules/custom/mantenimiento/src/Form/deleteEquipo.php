<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class deleteEquipo extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_deleteEquipo';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg=null) {
  	
  	$equipo = getRegistro('tp_equipo', 'id_equipo', $arg);

    $form['elemento_imagen'] = array(
      '#markup' => '<div class="eliminar-modal">El registro a eliminar es '. $equipo['nombre'].'. <br><br><i>Esta acción no se podrá deshacer.</i></div>' ,
    );

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'primary',
      '#attributes' => array(
        'class' => array('eliminar_registro')
      ),
    );

    $form['actions']['cancelar'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => array('mantenimiento_equipo_cancelar'),
      '#limit_validation_errors' => array(),
      '#attributes' => array(
        'class' => array('calcelar_accion')
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


  $id = $form_state->getValue('idregistro');

  $uid_user = \Drupal::currentUser()->id();

  $connection = \Drupal::database();

  $connection->update('tp_equipo')
    ->fields(
      array(
        'estado' => '0',
        'fecha_edicion' => time(),
        'uid_editor' => $uid_user
      )
    )
    ->condition('id_equipo', $id)
    ->execute();

  drupal_set_message("Se ha eliminado el registro ". $id );

  $form_state->setRedirect('mantenimiento.equipos');

  }

}
