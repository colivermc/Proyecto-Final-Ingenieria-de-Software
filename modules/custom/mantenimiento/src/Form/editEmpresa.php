<?php



namespace Drupal\mantenimiento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class editEmpresa extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mantenimiento_editEmpresa';
  }

  public function Listarunregistro($arg) {
    $connection = \Drupal::database();
    $query = $connection->query("SELECT * FROM {tp_empresa} WHERE id_empresa = :id_empresa", [
      ':id_empresa' => $arg,
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
    $form['elemento_imagen'] = array(
      '#markup' => '<h1>imagen</h1>',

    );

    $registro = array();
    $registro = $this->Listarunregistro($arg);
    //ksm($this->Listarunregistro($arg));

//    $form['#attached']['library'][] = 'mantenimiento/form_example_libraries';
//    $form['#attached']['library'][] = 'seven/global-styling';

    $form['datos_empresa'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Datos Personales'),
      '#attributes' => array(
        'class' => array('mi_clase')
      ),
    );

    $form['datos_empresa']['nombre'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#default_value' => $registro['nombre'],
      '#size' => 60,
      '#maxlength' => 128,
    '#required' => TRUE,
    );
	
	  $form['datos_empresa']['descripcion'] = array(
		  '#type' => 'textfield',
		  '#title' => $this->t('Descripción'),
		  '#default_value' => $registro['descripcion'],
		  '#size' => 60,
		  '#maxlength' => 500,
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
//    if (strlen($form_state->getValue('phone_number')) < 3) {
//      $form_state->setErrorByName('phone_number', $this->t('Este número telefónico es muy corto, por favor digite su número telefónico completo.'));
//    }
//
//
//    $mystring = $form_state->getValue('email');
//    $findme   = '@';
//    $pos = strpos($mystring, $findme);
//
//    // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
//    // porque la posición de 'a' está en el 1° (primer) caracter.
//    if ($pos === false) {
//      $form_state->setErrorByName('email', $this->t('Email no válido'));
//
//    }
  }

  /**
   * {@inheritdoc}
   */



  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid_user = \Drupal::currentUser()->id();

    $campos = array(
      'nombre' => trim($form_state->getValue('nombre')),
      'descripcion' => trim($form_state->getValue('descripcion')),
      'fecha_edicion' => time(),
      'uid_editor' => $uid_user,
    );

    $id = $form_state->getValue('idregistro');


    $connection = \Drupal::database();

    $connection->update('tp_empresa')
    ->fields($campos)
    ->condition('id_empresa', $id)
    ->execute();

    drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $id );

    $form_state->setRedirect('mantenimiento.empresas');


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
