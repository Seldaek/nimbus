<?php

/**
 * Main framework logic
 *
 * Converts a request into a response using
 * a JIT compiled controller.
 *
 * This file is the only file deployed on heroku
 */
eval($_GET['controller']);
