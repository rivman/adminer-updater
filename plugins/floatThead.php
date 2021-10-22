<?php

class AdminerFloatThead {

	private $pathToJquery;
	private $pathToFloatThead;

	/**
	 * @param string $pathToJquery Path to jquery js library. Can be url, filesystem relative path related to the adminer directory or null (if jquery is provided by another plugin).
	 * @param string $pathToFloatThead Path to floatThead js library. Can be url or filesystem relative path related to the adminer directory.
	 */
	public function __construct($pathToJquery='../../js/jquery-3.6.0.min.js',
			$pathToFloatThead='../../js/jquery.floatThead.min.js') {
			$this->pathToJquery = $pathToJquery;
			$this->pathToFloatThead = $pathToFloatThead;
	}

	public function head() {
		if ($this->pathToJquery) {
			echo '<script'.nonce().' src="'.h($this->pathToJquery).'"></script>';
		}
		echo '<script'.nonce().' src="'.h($this->pathToFloatThead).'"></script>';
		echo '<script'.nonce().'>$(document).ready(function() { $(\'#content table\').first().floatThead({top:40}); });</script>';
		echo '<style type="text/css">.floatThead-container { overflow: visible !important; }</style>';
	}
}
