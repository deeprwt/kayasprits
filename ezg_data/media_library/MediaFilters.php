<?php

/*
 * http://www.ezgenerator.com
 * Copyright (c) 2013 Image Line
 */

/**
 * Description of MediaFilters
 *
 * @author Joe
 */
class MediaFilters
{

	private $viewMode;

	public function __construct($viewMode)
	{
		$this->viewMode=$viewMode;
		return;
	}

	public function display($errors='')
	{
		include ML_ROOT_PATH.'view/filtersview.php';
	}
}

?>
