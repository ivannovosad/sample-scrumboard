<div style="display: none;">
    <div id="basic-modal-content_<?php echo $story_item_id; ?>" style="width:200px;height:90px;overflow:auto;">
        <form id="form-new-item_<?php echo $story_item_id; ?>" action="/?/item" method="post">
            <div class="add-task">
                <input type="hidden" name="story_id" value="<?php echo $story_item_id; ?>" />
                <div>
                    <label for="">Task name:</label>
                    <input id="item_name" type="text" name="item_name" value="" />
                </div>
                <br />
                <input id="btn-form-new_item" class="btn" type="submit" name="add" value="Add task" />
            </div>
        </form>
    </div>
</div>

    