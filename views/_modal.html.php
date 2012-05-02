<div style="display: none;">
    <div id="basic-modal-content" style="width:200px;height:90px;overflow:auto;">
        <form id="form-new_item" action="/?/item" method="post">
            <div class="add-task">
                <input type="hidden" name="story_id" value="<?php echo $story_item_id; ?>" />
                <div>
                    <label for="">Task name:</label>
                    <input id="item_name" type="text" name="item_name" value="" />
                </div>
                <br />
                <input id="btn-form-new_item" class="btn" type="submit" name="add" value="Add task" />
                <div id="status-message">Creating task...</div>
            </div>
        </form>
    </div>
</div>

    