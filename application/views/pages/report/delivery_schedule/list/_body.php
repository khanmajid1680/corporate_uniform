<tbody id="table_tbody" class="font-weight-bold" style="font-size: 0.7rem;">
    <?php 
        if(!empty($data)): 
            foreach (array_slice($data, 0, PER_PAGE) as $key => $value):
    ?>
                <tr>
                    <td width="3%"><?php echo $key+1; ?></td>
                    <td width="5%"><?php echo $value['entry_no']; ?></td>
                    <td width="5%">
                        <?php echo $value['customer_name']; ?>
                        <?php echo $value['apparel_data'];?>
                    </td>
                    <td width="5%"><?php echo $value['customer_mobile']; ?></td>
                    <td width="5%"><?php echo $value['entry_date']; ?></td>
                    <td width="5%"><?php echo $value['trial_date']; ?></td>
                    <td width="5%"><?php echo $value['delivery_date']; ?></td>
                    <td width="5%"><?php echo $value['notes']; ?></td>
                </tr>
    <?php 
            endforeach;
        else: 
    ?>
        <tr>
            <td class="text-danger font-weight-bold text-center" colspan="10">NO RECORD FOUND!!!</td>
        </tr>
    <?php endif; ?>
</tbody>