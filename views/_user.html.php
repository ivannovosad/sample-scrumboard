<tr>
	<td class="status <?= ($statesObject->ALL == $statesObject->PO) ? "po-done": "" ?>"></td>
	<td>
		<h3><?php echo $user; ?></h3>
		
		<?php if (($statesObject->ALL == $statesObject->PO)): ?>
		<div class="po-done">
			ALL TASKS PO DONE
		</div>
		<?php endif; ?>
	</td>
	<td>
		<div class="po-done"><?php echo $statesObject->PO; ?> PO done</div>
		<div class="dev-done"><?php echo $statesObject->DEV; ?> DEV done</div>
	</td>
</tr>
