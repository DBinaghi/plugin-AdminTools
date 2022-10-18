<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery.cookieBar({
			message: "<?php echo $this->message ?>",
			acceptText: "<?php echo __('I understand') ?>",
			policyButton: <?php echo $this->policyButton ?>,
			policyText: "<?php echo __('Privacy Policy') ?>",
			policyURL: "<?php echo $this->policyURL ?>",
			fixed: <?php echo $this->bottom ?>,
			bottom: <?php echo $this->bottom ?>
		});
	});
</script>
