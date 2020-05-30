<?php



	namespace Drupal\mantenimiento\Form;
	
	use Drupal\Core\Form\FormBase;
	use Drupal\Core\Form\FormStateInterface;
	use Symfony\Component\HttpFoundation\RedirectResponse;
	/**
	* Implements an example form.
	*/
	class editEquipo extends FormBase {
	
		/**
		 * {@inheritdoc}
		 */
		public function getFormId() {
		  return 'mantenimiento_editEquipo';
		}
		
		public function Listarunregistro($arg) {
		  $connection = \Drupal::database();
		  $query = $connection->query("SELECT * FROM {tp_equipo} WHERE id_equipo = :id_equipo", [
		    ':id_equipo' => $arg,
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
			//ksm($this->Listarunregistro($arg));
			
			$database = \Drupal::database();
			
			$form['datos_equipo']['equipo'] = array(
				'#type' => 'textfield',
				'#title' => $this->t('Nombre'),
				'#size' => 100,
				'#maxlength' => 128,
				'#required' => TRUE,
				'#default_value' => $registro['nombre'],
			);
			
			$queryUsuarios = $database->query("SELECT * FROM tp_responsable WHERE estado = 1");
			$resultUsuarios = $queryUsuarios->fetchAll();
			
			$usuarios = array();
			foreach ($resultUsuarios as $rowU){
				$rowU = (array)$rowU;
				
				$usuarios[$rowU['id_responsable']] = $rowU['username'];
			}
			
			$queryUsuariosSelect = $database->query("SELECT * FROM tp_user_equipo WHERE id_equipo = :id_equipo", array('id_equipo' => $registro['id_equipo']));
			$resultUsuariosSelect = $queryUsuariosSelect->fetchAll();
			
			$usuarios_seleccionado = array();
			foreach ($resultUsuariosSelect as $rowU){
				$rowU = (array)$rowU;
				
				$usuarios_seleccionado[$rowU['id_responsable']] = $rowU['id_responsable'];
			}
			
			$form['datos_equipo']['user'] = array(
				'#type' => 'select',
				'#title' => $this->t('Usuarios'),
				'#options' => $usuarios,
				'#multiple' => TRUE,
				'#required' => TRUE,
				'#default_value' => $usuarios_seleccionado,
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
				'#submit' => array('mantenimiento_equipo_cancelar'),
				'#limit_validation_errors' => array(),
				'#attributes' => array(
					'class' => array('cancelar_accion')
				),
			);
			
			$form['id_equipo'] = array(
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
			
			$connection = \Drupal::database();
			
			$uid_user = \Drupal::currentUser()->id();
			$id_equipo = (int)$form_state->getValue('id_equipo');
			
			$campos = array(
				'nombre' => trim($form_state->getValue('equipo')),
				'fecha_edicion' => time(),
				'uid_editor' => $uid_user,
			);
			
			$resultEquipo = $connection->update('tp_equipo')
				->fields($campos)
				->condition('id_equipo', $id_equipo, '=')
				->execute();
			
			
			$user = $form_state->getValue('user');
			
			$result_delete = $connection->delete('tp_user_equipo')
				->condition('id_equipo', $id_equipo, '=')
				->execute();
			
			foreach ($user as $user){
				$camposUsers = array(
					'id_responsable' => (int)$user,
					'id_equipo' => $id_equipo,
				);
				
				$result = $connection->insert('tp_user_equipo')
					->fields($camposUsers)
					->execute();
			}
			
			drupal_set_message("Datos guardados correctamente. Se ha actualizado el registro ". $form_state->getValue('equipo') );
			
			$form_state->setRedirect('mantenimiento.equipos');
		 
		}

	}
