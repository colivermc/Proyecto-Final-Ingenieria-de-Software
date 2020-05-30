<?php



namespace Drupal\calendario\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
/**
 * Implements an example form.
 */
class importProyecto extends FormBase {

	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'calendario_importProyecto';
	}
	
	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		
		$variables['http_host'] = $_SERVER['HTTP_HOST'];
		
		
		$form['proyecto']['import_file'] = [
			'#type' => 'managed_file',
			'#title' => t('Adjuntar imagen'),
			'#multiple' => FALSE,
			'#required' => FALSE,
			'#upload_validators' => array(
				'file_validate_extensions' => array('csv'),
				'file_validate_size' => array(25600000),
			),
			'#upload_location' => 'public://importaciones_de_proyectos',
			'#attributes' => array(
				'class' => array('imagen-adjunta')
			),
		];
		
		$form['proyecto']['download'] = [
			'#type' => 'item',
			'#markup' => "<a class='btn btn-success btn-regresar' download='proyectos_import.csv' href='/flagger/modules/custom/calendario/includes/proyectos_import.csv'>
          <span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span>
          Descargar formato</a>",
		];
		
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
		  '#submit' => array('calendario_proyecto_cancelar'),
		  '#limit_validation_errors' => array(),
		  '#attributes' => array(
		    'class' => array('cancelar_accion')
		  ),
		);
		
		$form['errores'] = array(
			'#type' => 'hidden',
			'#value' => 0
		);
		
		return $form;
	}
	
	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		
		$errores = $form_state->getValue('errores');
		
		$database = \Drupal::database();
		
		if (!empty($form_state->getValue('import_file'))){
			$fid_file = $form_state->getValue('import_file');
			$file = File::load($fid_file[0]);
			
			// genero url y fid
			$file_uri = $file->getFileUri();
			$file_url = file_create_url($file_uri);
			
			$file = fopen($file_url, "r");
			
			$linea = 0;
			
			while (!feof($file)) {
				$customer = fgetcsv($file, 0, ",");
				
				if ($linea > 0) { // valido que no tome en cuenta el header
					
					if (!is_numeric($customer[2])){
						drupal_set_message("Error en la linea ".$linea." en el campo DURACION. El valor ingresado no es numerico", 'error');
					}
					
					// busco pais
					$queryPais = $database->query("SELECT * FROM {tp_pais} WHERE nombre = :nombre", [
						':nombre' => trim((string)$customer[4]),
					]);
					$resultPais = $queryPais->fetchAll();
					
					if (count($resultPais) < 1){
						drupal_set_message("Error en la linea ".$linea." en el campo NOMBRE DEL PAIS.", 'error');
						$form_state->setValue('errores', $errores+1);
					}
					
					// busco canal
					$queryCanal = $database->query("SELECT * FROM {tp_canal} WHERE canal = :canal", [
						':canal' => (int)$customer[5],
					]);
					$resultCanal = $queryCanal->fetchAll();
					
					if (count($resultCanal) < 1){
						drupal_set_message("Error en la linea ".$linea." en el campo CANAL.", 'error');
						$form_state->setValue('errores', $errores+1);
					}
					
					
					// busco empresa
					$queryEmpresa = $database->query("SELECT * FROM {tp_empresa} WHERE nombre = :nombre", [
						':nombre' => trim((string)$customer[6]),
					]);
					$resultEmpresa = $queryEmpresa->fetchAll();
					
					if (count($resultEmpresa) < 1){
						drupal_set_message("Error en la linea ".$linea." en el campo NOMBRE DEL LA EMPRESA.", 'error');
						$form_state->setValue('errores', $errores+1);
					}
					
					
				}
				$linea = $linea + 1;
			}
		}
		
	}
	
	/**
	* {@inheritdoc}
	*/



    public function submitForm(array &$form, FormStateInterface $form_state) {
	
	    $database = \Drupal::database();
    	
    	$errores = $form_state->getValue('errores');
    	
    	if ($errores <= 0){
		
		    $uid_user = \Drupal::currentUser()->id();
		    $fid_file = $form_state->getValue('import_file');
		    $file = File::load($fid_file[0]);
		    $file->setPermanent();
		    $file->save();
		
		    // genero url y fid
		    $file_uri = $file->getFileUri();
		    $file_url = file_create_url($file_uri);
		
		    $file = fopen($file_url, "r");
		
		    $header = 0;
		
		    while (!feof($file)) {
			    $customer = fgetcsv($file, 0, ",");
			
			    if ($header > 0){ // valido que no tome en cuenta el header
				
				    // busco pais
				    $queryPais = $database->query("SELECT * FROM {tp_pais} WHERE nombre = :nombre", [
					    ':nombre' => trim((string)$customer[4]),
				    ]);
				    $resultPais = $queryPais->fetchAll();
				
				    $id_pais = 0;
				    foreach ($resultPais as $row){
					    $row = (array) $row;
					    $id_pais = (int)$row['id_pais'];
				    }
				
				    // busco canal
				    $queryCanal = $database->query("SELECT * FROM {tp_canal} WHERE canal = :canal", [
					    ':canal' => (int)$customer[5],
				    ]);
				    $resultCanal = $queryCanal->fetchAll();
				
				    $id_canal = 0;
				    foreach ($resultCanal as $row){
					    $row = (array) $row;
					    $id_canal = (int)$row['id_canal'];
				    }
				
				    // busco empresa
				    $queryEmpresa = $database->query("SELECT * FROM {tp_empresa} WHERE nombre = :nombre", [
					    ':nombre' => trim((string)$customer[6]),
				    ]);
				    $resultEmpresa = $queryEmpresa->fetchAll();
				
				    $id_empresa = 0;
				    foreach ($resultEmpresa as $row){
					    $row = (array) $row;
					    $id_empresa = (int)$row['id_empresa'];
				    }
				
				    $fecha_transmision = (string)str_replace("/", "-", $customer[1]);
				    $fecha_finalizacion = (string)str_replace("/", "-", $customer[3]);
				
				    $campos = array(
					    'proyecto' => trim((string)$customer[0]),
					    'fecha_transmision' => strtotime($fecha_transmision),
					    'duracion' => trim((string)$customer[2]),
					    'fecha_finalizacion' => strtotime($fecha_finalizacion),
					    'id_pais' => $id_pais,
					    'id_canal' => $id_canal,
					    'estado_transmision' => 0, // 0 = no transmitido
					    'id_empresa' => $id_empresa,
					    'fecha_creacion' => time(),
					    'uid_creador' => $uid_user,
				    );
				   
				
				    $result = $database->insert('tp_proyecto')
					    ->fields($campos)
					    ->execute();
			    }
			    $header = $header + 1;
		    }
		
		    fclose($file);
		
		    drupal_set_message('Archivo importado con exito');
		
		    $form_state->setRedirect('calendario.proyecto');
    		
	    }else{
    		drupal_set_message("No se pudo importar archivo .CSV porque cuenta con errores", 'error');
	    }
	
	    

    }

}
