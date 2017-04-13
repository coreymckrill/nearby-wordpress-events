<!DOCTYPE html>
<html>
<head>
	<!--
	 !-- To run these, install the plugin into a checkout of develop.git.wordpress.org,
	 !-- and then browse to /src/wp-content/plugins/nearby-wp-events/tests/qunit/
	 -->

	<title>Nearby WordPress Events QUnit Test Suite</title>

	<!-- Dependencies -->
	<script src="/src/wp-includes/js/jquery/jquery.js"></script>
	<script src="/src/wp-includes/js/jquery/ui/core.js"></script>
	<script src="/src/wp-includes/js/underscore.min.js"></script>
	<script src="/src/wp-includes/js/backbone.min.js"></script>
	<script src="/src/wp-includes/js/wp-backbone.js"></script>
	<script>
		window._wpUtilSettings = {
			'ajax' : {
				'url' : '\/src\/wp-admin\/admin-ajax.php'
			}
		};
	</script>
	<script src="/src/wp-includes/js/wp-util.js"></script>
	<script src="/src/wp-includes/js/wp-a11y.js"></script>

	<!-- QUnit -->
	<link rel="stylesheet" href="/tests/qunit/vendor/qunit.css" type="text/css" media="screen" />
	<script src="/tests/qunit/vendor/qunit.js"></script>
	<script src="/tests/qunit/vendor/sinon.js"></script>
	<script src="/tests/qunit/vendor/sinon-qunit.js"></script>
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
