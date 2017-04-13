<?php

	$core_tests_base_url = 'http://wp-develop.dev';
	$core_qunit_base_url = $core_tests_base_url . '/tests/qunit';

?><!DOCTYPE html>
<html>
<head>
	<title>Nearby WordPress Events QUnit Test Suite</title>

	<!-- Dependencies -->
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/jquery/jquery.js"></script>
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/jquery/ui/core.js"></script>
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/underscore.min.js"></script>
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/backbone.min.js"></script>
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/wp-backbone.js"></script>\
	<script>
		window._wpUtilSettings = {
			'ajax' : {
				'url' : '\/wp-admin\/admin-ajax.php'
			}
		};
	</script>
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/wp-util.js"></script>
	<script src="<?php echo $core_tests_base_url; ?>/src/wp-includes/js/wp-a11y.js"></script>

	<!-- QUnit -->
	<link rel="stylesheet" href="<?php echo $core_qunit_base_url; ?>/vendor/qunit.css" type="text/css" media="screen" />
	<script src="<?php echo $core_qunit_base_url; ?>/vendor/qunit.js"></script>
	<script src="<?php echo $core_qunit_base_url; ?>/vendor/sinon.js"></script>
	<script src="<?php echo $core_qunit_base_url; ?>/vendor/sinon-qunit.js"></script>
	<script>QUnit.config.hidepassed = false;</script>
</head>

<body>
	<div id="qunit"></div>

	<!-- Tested files -->
	<script src="../../js/dashboard.js"></script>

	<!-- Unit tests -->
	<script src="test-dashboard.js"></script>
</body>
</html>
