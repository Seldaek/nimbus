<?php

/**
 * Development front controller
 *
 * Run the deployment script to publish to the cloud
 */
eval(file_get_contents(empty($_GET['controller']) ? 'controllers/default.php' : 'controllers/' . $_GET['controller']));
