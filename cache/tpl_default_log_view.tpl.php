<p><a href="?do=edit&page=log&date=<?php echo (isset($this->_rootref['DATE'])) ? $this->_rootref['DATE'] : ''; ?>">Edit Log</a></p>
<?php $_items_count = (isset($this->_tpldata['items'])) ? sizeof($this->_tpldata['items']) : 0;if ($_items_count) {for ($_items_i = 0; $_items_i < $_items_count; ++$_items_i){$_items_val = &$this->_tpldata['items'][$_items_i]; ?>
	<h1><?php echo $_items_val['EXERCISE']; ?></h1><p>Volume: <?php echo $_items_val['VOLUME']; ?> - Reps: <?php echo $_items_val['REPS']; ?> - Sets: <?php echo $_items_val['SETS']; ?></p>
	<?php $_sets_count = (isset($_items_val['sets'])) ? sizeof($_items_val['sets']) : 0;if ($_sets_count) {for ($_sets_i = 0; $_sets_i < $_sets_count; ++$_sets_i){$_sets_val = &$_items_val['sets'][$_sets_i]; ?>
		<p><?php echo $_sets_val['WEIGHT']; ?> x <?php echo $_sets_val['REPS']; ?> x <?php echo $_sets_val['SETS']; ?> - <?php echo $_sets_val['COMMENT']; ?></p>
	<?php }} ?>
	<p><?php echo $_items_val['COMMENT']; ?></p>
<?php }} ?>