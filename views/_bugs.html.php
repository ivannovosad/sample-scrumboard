<h2>BUGS</h2>
<table>
	<tr>
        <th class="not-started">Not started</th>
        <td class="not-started"><?php echo count($sprint->get_tasks(array(STATE_NOT_STARTED), true)); ?></td>
    </tr>
	<tr>
        <th class="dev-started">Started</th>
        <td class="dev-started"><?php echo count($sprint->get_tasks(array(STATE_DEV_STARTED), true)); ?></td>
    </tr>
	<tr>
        <th class="dev-done">Dev done</th>
        <td class="dev-done"><?php echo count($sprint->get_tasks(array(STATE_DEV_DONE), true)); ?></td>
    </tr>
	<tr>
        <th class="qa-done">QA done</th>
        <td class="qa-done"><?php echo count($sprint->get_tasks(array(STATE_QA_DONE), true)); ?></td>
    </tr>
	<tr>
        <th class="po-done">PO done</th>
        <td class="po-done"><?php echo count($sprint->get_tasks(array(STATE_PO_DONE), true)); ?></td>
    </tr>
	<tr>
        <th>ALL</th>
        <td>
            <?php echo count($sprint->get_tasks(
                array(STATE_NOT_STARTED, STATE_DEV_STARTED, STATE_DEV_DONE, STATE_QA_DONE, STATE_PO_DONE),
            true)); ?>
        </td>
    </tr>
</table>
