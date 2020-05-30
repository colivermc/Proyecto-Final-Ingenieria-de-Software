<?php



namespace Drupal\checklist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class checkinTarea extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'checklist_checkinTarea';
	}
	
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $id_proyecto = null, $id_tarea = null) {
	
		$miRegistro = getRegistro("tp_tarea", "id_tarea", $id_tarea);
		
		$form['elemento_imagen'] = array(
		  '#markup' => 'La tarea: '. $miRegistro['nombre'].'. se cambiará a estado FINALIZADO <br><br><i>Esta acción no se podrá deshacer.</i> ',
		);
		
		$form['finalizar']['comentario'] = array(
			'#type' => 'textfield',
			'#title' => $this->t('Comentario'),
			'#size' => 60,
			'#maxlength' => 450,
			'#required' => TRUE,
		);
		
		$form['finalizar']['#type'] = 'finalizar_tarea';
		
		$form['finalizar']['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Finalizar'),
			'#button_type' => 'primary',
			'#attributes' => array(
				'class' => array('finaliz-tarea')
			),
		);
		
		$form['finalizar']['cancelar'] = array(
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
		
		$campos_tarea = array(
			'checkin' => 1,
			'comentario_finalizacion' => trim($form_state->getValue('comentario')),
			'fecha_edicion' => time(),
			'uid_editor' => $uid_user,
		);
		
		$resulTarea = $connection->update('tp_tarea')
			->fields($campos_tarea)
			->condition('id_proyecto', $id_proyecto)
			->condition('id_tarea', $id_tarea)
			->execute();
		
		$miRegistro = getRegistro("tp_tarea", "id_tarea", $id_tarea);
		
		drupal_set_message("La tarea con el nombre: ". $miRegistro['nombre'] . " se a finalizado");
		
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
