<thead>
    <th width="3%">#</th>
    <th width="10%">apparel</th>
    <th width="5%">status</th>
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