<script src="<?php echo assets('dist/js/'.$menu.'/'.$sub_menu.'.js?v=1')?>"></script>
<script type="text/javascript">
    setTimeout(() => {
        $("#_name").select2(
            select2_default({
                url: `<?php echo $menu; ?>/<?php echo $sub_menu; ?>/get_select2/_name`,
                placeholder: "fabric",
            })
        ).on("change", () => trigger_search());
    }, RELOAD_TIME)
</script>