<?php



namespace Drupal\checklist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class deleteTarea extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'checklist_deleteTarea';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id_proyecto = null, $id_tarea = null) {

    $miRegistro = getRegistro("tp_tarea", "id_tarea", $id_tarea);

    $form['elemento_imagen'] = array(
      '#markup' => '<div class="eliminar-modal">El registro a eliminar es '. $miRegistro['nombre'].'. <br><br><i>Esta acción no se podrá deshacer.</i></div>',
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
      '#submit' => array('::checklist_tarea_cancelar'),
      '#id_proyecto' => $id_proyecto,
      '#limit_validation_errors' => array(),
      '#attributes' => array(
        'class' => array('cancelar_accion')
      ),
    );

    $form['id_proyecto'] = array(
      '#type' => 'hidden',
      '#value' => $id_proyecto
    );

    $form['id_tarea'] = array(
      '#type' => 'hidden',
      '#value' => $id_tarea
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

    $id_proyecto = $form_state->getValue('id_proyecto');
    $id_tarea = $form_state->getValue('id_tarea');

    $uid_user = \Drupal::currentUser()->id();

    $connection = \Drupal::database();

//    $connection->delete('tp_tarea')
//      ->condition('id_tarea', $id_tarea)
//      ->execute();
//
//    $connection->delete('tp_sla')
//      ->condition('id_tarea', $id_tarea)
//      ->execute();

    $connection->update('tp_tarea')
      ->fields(
        array('estado' => '0',
          'fecha_edicion' => time(),
          'uid_editor' => $uid_user
        )
      )
      ->condition('id_tarea', $id_tarea)
      ->execute();

    $connection->update('tp_sla')
      ->fields(
        array('estado' => '0',
          'fecha_edicion' => time(),
          'uid_editor' => $uid_user
        )
      )
      ->condition('id_tarea', $id_tarea)
      ->execute();

    $miRegistro = getRegistro("tp_tarea", "id_tarea", $id_tarea);

    drupal_set_message("Se ha eliminado el registro ". $miRegistro['nombre'] );

    $form_state->setRedirect('checklist.tareas', array('id_proyecto' => $id_proyecto));

  }

  public function checklist_tarea_cancelar (array &$form, FormStateInterface $form_state) {

    drupal_set_message('Acción Cancelada!','error');

    $id_proyecto  = $form_state->getTriggeringElement()['#id_proyecto'];

    global $base_url;

    $response = new RedirectResponse($base_url.'/proyecto/'.$id_proyecto.'/tareas');
    $response->send();
    return;

  }

}
