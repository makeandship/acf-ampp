<?php 

class MedicinesApi { 
	const MEDICINES_SERVICE_SCHEME = 'http'; 
	const MEDICINES_SERVICE_HOST = 'api.medicines.makeandship.com';
	     
	public function get_endpoint($path, $query) {
		$query_string = null;
		if ($query) {
			$query_string = http_build_query($query);
		}

		$uri = self::MEDICINES_SERVICE_SCHEME.'://'.self::MEDICINES_SERVICE_HOST.$path;
		if ($query_string) {
			$uri = $uri.'?'.$query_string;
		}
		
		if ($path == '/virtual_therapeutic_moieties.js') {
			$uri = 'http://telemachus.local/wp/vtms.json';
		}
		else if (substr($path, 0, strlen('/actual_medicinal_product_packs')) === '/actual_medicinal_product_packs') {
			$uri = 'http://telemachus.local/wp/ampp.json';
		}

		return $uri;
	}

    public function ampps($query) {
		// full response for AMPP 
		$query['full'] = true;
		
    	$uri = $this->get_endpoint('/virtual_therapeutic_moieties.js', $query);

		$request_args = array(
			'timeout' => 30	
		);
        $request = wp_remote_get( $uri, $request_args );
		$response = json_decode( $request['body'], true );
		
		$vtms = $response['vtms'];
		
		$results = array();
		$index = 0;
		
		foreach ($vtms as $vtm) {
			$vtm_name = $vtm['name'];
			$ampps = array(
				"text"=> $vtm_name,
				"children" => array()
			);
			foreach($vtm['virtual_medicinal_products'] as $vmp) {
				foreach($vmp['virtual_medicinal_product_packs'] as $vmpp) {
					foreach($vmpp['actual_medicinal_product_packs'] as $ampp) {
						$ampp_id = $ampp['id'];
						$ampp_name = $ampp['name'];
						
						$title = $ampp_name;
						
						$entry = array("id" => $ampp_id, "text" => $title);
						array_push($ampps["children"], $entry);
					}
				}
			}
			
			array_push($results, $ampps);
		}
		
		return $results;
    } 
	
	public function ampp($id) {
    	$uri = $this->get_endpoint('/actual_medicinal_product_packs/'.$id.'.js', null);

		$request_args = array(
			'timeout' => 30	
		);
        $request = wp_remote_get( $uri, $request_args );
		$response = json_decode( $request['body'], true );
		
		return $response;
	}
} 