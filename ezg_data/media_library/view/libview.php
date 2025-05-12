<?php

$this->buildMlHead();
$this->buildMlBodyBegin();
$nav = Navigation::pageCA($folder->getTotalFilesCount(), '', 20, $folder->getCPage());
$cap_arrays = array('<input type="checkbox" id="checkall" name="checkall" value="checkall"/>', LBL_TITLE, LBL_TYPE, LBL_FILE_SIZE, LBL_FILE_DATE);
$i = 0;
$table_data = array();
$recentlyAdded = $folder->getRecentlyAdded();
foreach ($files as $file) {
	 $i++;
	 $row_data = array();
	 if ($file->getName() == '..')
		  $row_data[] = '';
	 else
		  $row_data[] = '<input class="mediacheckbox" type="checkbox" name="media[]" value="' . $file->getFullName() . '" />';
	 $mbox = '';
	 $rel = '';
	 if (preg_match('/(jpg|jpeg|png|bmp|gif)$/i', $file->getType())) {
		  $mbox = 'mbox ';
		  $rel = 'noDesc,width:800';
	 }

	 $entryData = '
                <a class="' . $mbox . 'entryhref ' . ($file->getFullName() == $recentlyAdded ? 'recent' : '') . '" target="_self" href="' . $file->getFullPath() . '" rel="' . $rel . '">
                <div class="rvts8">
                <div class="mediaimg">';
	 if (preg_match('/(jpg|jpeg|png|bmp|gif)$/i', $file->getType())) {
		  $entryData .= '<img class="img-thumb" src="' . $file->getFullPath(true) . '" />';
	 } elseif (preg_match('/(mp3|wav)$/i', $file->getType())) {
		  $entryData .= '<i class="fa fa-music fa-2x fa-fw playBtn" rel="audio/' . $file->getType() . '|' . $file->getFullPath() . '"></i>';
	 } elseif (preg_match('/(avi|cam|flv|mov|mpeg|mpg|swf|wmv|mp4|ogg|webm)$/i', $file->getType())) {
		  $entryData .= '<i class="fa fa-video-camera fa-2x fa-fw playBtn" rel="video/' . $file->getType() . '|' . $file->getFullPath() . '"></i>';
	 } elseif ($file->getType() == 'dir') {
		  if ($file->getName() == '..') {
				$entryData .= '<span class="fa-stack fa-fw libview_stack">
                          <i class="fa fa-folder-o fa-stack-2x fa-fw"></i>
                          <i class="fa fa-level-up fa-stack-1x fa-fw libview_level_up"></i>
                        </span>';
		  } else {
				$entryData .= '<i class="fa fa-folder-o fa-3x fa-fw"></i>';
		  }
	 } else {
		  $entryData .= '<i class="fa fa-file-text-o fa-2x fa-fw"></i>';
	 }
	 $entryData .= '</div>';
	 $entryData.='&nbsp;' . $file->getName(TRUE);
	 $entryData.='</div></a>';

	 if ($file->getName() != '..') {

		  $nav_arr[LBL_DELETE] = array('class' => 'entrynavElem delBtn', 'url' => 'javascript: void(0)', 'extra_tags' => '');
		  if ($file->getType() != 'dir') {

				$fname = $file->getFullName();
				$path = $file->getPath();
				if (ROOT_PATH == '') { //ca is loaded from folder withtou base tag
					 $fname = str_replace('../', '', $fname);
					 $path = str_replace('../', '', $path);
				}
				$nav_arr[LBL_DOWNLOAD] = array(
					 'class' => 'entrynavElem downloadBtn2',
					 'url' => '../documents/utils.php?action=download&filename=' . $fname . '&dir=' . $path,
					 'extra_tags' => '');
		  }
		  $row_data[] = array($entryData . '<div style="clear:left"></div>', $nav_arr);
	 } else
		  $row_data[] = $entryData;
	 $row_data[] = '<span class="rvts8">' . ($file->getType() == 'dir' ? '' : $file->getType()) . '</span>';
	 $row_data[] = '<span class="rvts8">' . ($file->getName() == '..' ? '' : $file->getSize(true)) . '</span>';
	 $row_data[] = '<span class="rvts8">' . ($file->getName() == '..' ? '' : $file->getModedDate()) . '</span>';
	 $table_data[] = $row_data;
}

$append = '
            <button class="delete_btn">' . LBL_DELETE . '</button>
            <button class="addFldr_btn">' . LBL_ADD_FOLDER . '</button>
            ' . $folder->prepareProtectButton() . '
            ';
echo Builder::adminTable($nav, $cap_arrays, $table_data, $append);
$this->buildbuildMlBodyEnd();
?>

