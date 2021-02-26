<?php
/*
	Plugin Name: WYSIWYG Math Editor
	Plugin URI: 
	Plugin Description: WYSIWYG Math Editor
	Plugin Version: 1.1.0
	Plugin Date: 2021-02-26
	Plugin Author: Thibault Duponchelle and Chepheus
	Plugin Author URI: https://github.com/thibaultduponchelle/q2a-ckeditor-latex
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.5.1
	Plugin Update Check URI: https://raw.githubusercontent.com/thibaultduponchelle/q2a-ckeditor-latex/master/qa-plugin.php
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

qa_register_plugin_module('editor', 'qa-matheditor.php', 'qa_matheditor', 'MathEditor');
qa_register_plugin_module('page', 'qa-wysiwyg-matheditor-upload.php', 'qa_wysiwyg_matheditor_upload', 'WYSIWYG MathEditor Upload');
qa_register_plugin_phrases('qa-wysiwyg-lang-*.php', 'wysiwyg-matheditor');

