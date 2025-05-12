<?php

/*
 * http://www.ezgenerator.com
 * Copyright (c) 2013 Image Line
 */

/**
 * Description of MediaNavigation
 *
 * @author Joe
 */
class MediaNavigation
{
	private $pagesCount;
	private $currentPage;

	public function __construct($total,$cPage)
	{
		$this->pagesCount=$total;
		$this->currentPage=$cPage>$total?$total:$cPage;
	}

	public function display()
	{
		if($this->pagesCount<2)
			return; //don't display navigation if there is only 1 page
		include ML_ROOT_PATH.'view/navview.php';
	}
}

?>
