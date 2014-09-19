<?php

namespace multikanban\multikanban\Api;

class ApiProblem{

	const TYPE_VALIDATION_ERROR = 'validation_error';
	const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';

	private static $titles = array(
		self::TYPE_VALIDATION_ERROR => 'There was a validation error',
		self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent'
	);

	private $statusCode;
	private $type;
	private $title;

	private $extraData = array();

	public function __construct($statusCode, $type){

		$this->statusCode = $statusCode;
		$this->type = $type;

		if(!isset(self::$titles[$type])){
			throw new \Exception(
				sprintf(
					'No title for type "%s". Did you make it up?',
					$type
				)
			);
		}

		$this->title = self::$titles[$type];
	}

	public function toArray(){

		return array_merge(
			$this->extraData,
			array(
				'status' => $this->statusCode,
				'type' => $this->type,
				'title' => $this->title
			)
		);
	}

	public function set($name, $value){

		$this->extraData[$name] = $value;
	}

	public function getStatusCode(){

		return $this->statusCode;
	}
}