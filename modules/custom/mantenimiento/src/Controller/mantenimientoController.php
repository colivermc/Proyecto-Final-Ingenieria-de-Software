<?php

namespace Drupal\mantenimiento\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\mantenimiento\Form\editEmpresa;
use http\Client\Curl\User;


class mantenimientoController extends ControllerBase
{
	
	public function menu_mantenimiento()
	{
		
		global $base_url;
		
		$contenido = array();
		
		$registro = "prueba";
		
		$contenido[] = [
			'#theme' => 'mantenimiento_template',
			'#test_var' => $registro,
		];
		
		
		return $contenido;
		
	}
	
}
