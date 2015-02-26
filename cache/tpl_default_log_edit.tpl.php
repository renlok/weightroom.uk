<?php if ($this->_rootref['ERROR']) {  ?>
	<p>logging faied</p>
<?php } ?>
<form action="?page=log&do=edit<?php if ($this->_rootref['DATE'] != ('')) {  ?>&date=<?php echo (isset($this->_rootref['DATE'])) ? $this->_rootref['DATE'] : ''; } ?>" method="post">
enter log:<br>
<textarea rows="30" cols="70" name="log">
<?php echo (isset($this->_rootref['LOG'])) ? $this->_rootref['LOG'] : ''; ?>
</textarea>
weight:
<input type="text" name="weight" value="<?php echo (isset($this->_rootref['WEIGHT'])) ? $this->_rootref['WEIGHT'] : ''; ?>"> kg
<input type="hidden" name="csrftoken" value="<?php echo (isset($this->_rootref['_CSRFTOKEN'])) ? $this->_rootref['_CSRFTOKEN'] : ''; ?>">
<input type="submit" name="action" value="add/edit log">
</form>