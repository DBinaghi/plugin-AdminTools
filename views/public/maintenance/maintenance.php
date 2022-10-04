<title><?php echo __('Maintenance Mode'); ?></title>

<style>
	#maintenance_outer {
		display: table;
		position: absolute;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%;
	}

	#maintenance_middle {
		display: table-cell;
		vertical-align: middle;
	}

	#maintenance_inner {
		margin-left: auto;
		margin-right: auto;
		width: 75%;
		max-width: 800px;
		padding: 20px; 
		border: 2px solid black; 
		border-radius: 10px; 
		background-color: lightyellow;
	}

	.maintenance_text {
		text-align: left; 
		font: 32px Helvetica, sans-serif; 
	}

	.maintenance_text h1 {
		font-size: 1.5em; 
		margin: 0;
	}

	.maintenance_text h2 {
		font-size: 0.8em; 
		margin-bottom: 20px;
		border-bottom: 2px solid black;
		text-align: right;
		font-style: italic;
	}

	.centered {
		text-align: center;
	}
</style>

<div id="maintenance_outer">
	<div id="maintenance_middle">
		<div id="maintenance_inner" class="maintenance_text">
			<h2><?php echo get_option('site_title'); ?></h2>
			<h1 class="centered"><?php echo get_option('admin_tools_maintenance_title'); ?></h1>
			<p><?php echo get_option('admin_tools_maintenance_message'); ?> </p>
		</div>
	</div>
</div>
