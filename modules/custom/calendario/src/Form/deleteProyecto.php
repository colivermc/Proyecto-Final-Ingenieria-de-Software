<?php



namespace Drupal\calendario\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class deleteProyecto extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'calendario_deleteProyecto';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $arg=null) {

    $miRegistro = getRegistro("tp_proyecto", "id_proyecto", $arg);

    $form['elemento_imagen'] = array(
      '#markup' => '<div class="eliminar-modal">El registro a eliminar es '. $miRegistro['proyecto'].'. <br><br><i>Esta acción no se podrá deshacer.</i></div>' ,
    );

//    $form['#attached']['library'][] = 'mantenimiento/form_example_libraries';
//    $form['#attached']['library'][] = 'seven/global-styling';

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
      '#submit' => array('calendario_proyecto_cancelar'),
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

    $id = $form_state->getValue('idregistro');

    $uid_user = \Drupal::currentUser()->id();

    $connection = \Drupal::database();

    $connection->update('tp_proyecto')
      ->fields(
        array('estado' => '0',
          'fecha_edicion' => time(),
          'uid_editor' => $uid_user
        )
      )
      ->condition('id_proyecto', $id)
      ->execute();

    $miRegistro = getRegistro("tp_proyecto", "id_proyecto", $id);

    drupal_set_message("Se ha eliminado el registro ". $miRegistro['proyecto'] );

    $form_state->setRedirect('calendario.proyecto');


  //ksm($campos);
      /*
      drupal_set_message($this->t('Su número telefónico es: @number', array('@number' => $form_state->getValue('phone_number'))));

      global $base_url;

      //dpm($base_url);

      $response = new RedirectResponse($base_url);
      $response->send();
      return;
  */
  }

}
