<tbody id="table_tbody" class="font-weight-bold" style="font-size: 0.7rem;">
    <?php 
        if(!empty($data)): 
            foreach (array_slice($data, 0, PER_PAGE) as $key => $value):
    ?>
                <tr>
                    <td width="3%"><?php echo $key+1; ?></td>
                    <td width="10%"><?php echo $value['customer_name']; ?></td>
                    <td width="5%"><?php echo $value['anniversary_date']; ?></td>
                    <td width="5%"><?php echo $value['customer_mobile']; ?></td>
                    <td width="10%" class="text-lowercase"><?php echo $value['customer_email']; ?></td>
                    <td width="15%"><?php echo $value['customer_address']; ?></td>
                    <td width="8%"><?php echo $value['city_name']; ?></td>
                    <td width="8%"><?php echo $value['state_name']; ?></td>
                    <td width="8%"><?php echo $value['country_name']; ?></td>
                    <td width="5%"><?php echo $value['customer_pincode']; ?></td>
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