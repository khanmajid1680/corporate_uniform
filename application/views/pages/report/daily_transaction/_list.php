<?php 
	$this->load->view('templates/header'); 
	$search_status 	= !isset($_GET['search_status']);
?>
<script>
    let link 		= "<?php echo $menu; ?>";
    let sub_link 	= "<?php echo $sub_menu; ?>";
</script>
<section class="container-fluid sticky_top">
	<?php $this->load->view('pages/'.$menu.'/'.$sub_menu.'/list/_navbar'); ?>
</section>
<section class="container-fluid">
	<div class="row">
		<div class="col-12">
			<table class="table table-sm table-dark text-uppercase">
				<?php $this->load->view('pages/'.$menu.'/'.$sub_menu.'/list/_header'); ?>
			</table>
			<div class="list_wrapper">
				<table class="table table-sm table-hover text-uppercase" id="table_reload">
					<?php $this->load->view('pages/'.$menu.'/'.$sub_menu.'/list/_body', ['data' => $data]); ?>
				</table>
			</div>
		</div>
	</div>
</section>

<?php $this->load->view('templates/footer'); ?>
<?php $this->load->view('pages/'.$menu.'/'.$sub_menu.'/_filter', ['filters' => isset($data['filter']) ? $data['filter'] : []]); ?>
<?php $this->load->view('pages/'.$menu.'/'.$sub_menu.'/list/_footer'); ?>
</body>
</html>