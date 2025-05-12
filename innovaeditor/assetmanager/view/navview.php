<div class="navigation">
	<?php
	$startPage=1;
	if($this->currentPage-2 > 2)
	{
		$startPage=$this->currentPage-2;
		?>
		<div class='naventry' >
			<?php	echo 1;?>
		</div>
		<div style="float:left;">...</div>
		<?php
	}
	$endPage = $this->currentPage+2 < $this->pagesCount-1? $this->currentPage+2:$this->pagesCount;
	for($i=$startPage;$i<=$endPage;$i++)
	{
	?>
	<div class='<?php echo $i==$this->currentPage?'activeNav':'';?> naventry' >
		<?php	echo $i;?>
	</div>
	<?php
	}

	if($endPage!=$this->pagesCount)
	{
		?>
		<div style="float:left;">...</div>
		<div class='naventry' >
			<?php	echo $this->pagesCount;?>
		</div>
		<?php
	}
	?>
	<div class="clear-both"></div>
</div>
