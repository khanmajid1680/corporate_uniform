<thead>
    <th width="3%">#</th>
    <th width="10%"><?php echo str_replace('_', ' ', $sub_menu); ?></th>
    <th width="10%">category</th>
    <th width="5%">charges</th>
    <th width="5%">status</th>
    <!-- <th width="3%">process</th> -->
    <?php if(in_array('read', $action_data)): ?>
        <th width="3%">view</th> 
    <?php endif; ?>
    <?php if(in_array('edit', $action_data)): ?>
        <th width="3%">edit</th> 
    <?php endif; ?>
    <?php if(in_array('delete', $action_data)): ?>
        <th width="3%">delete</th>
    <?php endif; ?>
</thead>