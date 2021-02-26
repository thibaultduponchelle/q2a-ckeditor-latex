<?php

// Duplicate of /qa-plugin/wysiwyg-editor/qa-wysiwyg-upload.php
// it was necessary to do it in order to make matheditor upload independent from another plugins

class qa_wysiwyg_matheditor_upload
{
	public function match_request($request)
	{
		return $request === 'wysiwyg-editor-upload';
	}

	public function process_request($request)
	{
		$message = '';
		$url = '';

		if (is_array($_FILES) && count($_FILES)) {
			if (qa_opt('wysiwyg_editor_upload_images')) {
				require_once QA_INCLUDE_DIR . 'app/upload.php';

				$onlyImage = qa_get('qa_only_image');
				$upload = qa_upload_file_one(
					qa_opt('wysiwyg_editor_upload_max_size'),
					$onlyImage || !qa_opt('wysiwyg_editor_upload_all'),
					$onlyImage ? 600 : null, // max width if it's an image upload
					null // no max height
				);

				if (isset($upload['error'])) {
					$message = $upload['error'];
				} else {
					$url = $upload['bloburl'];
				}
			} else {
				$message = qa_lang_html('users/no_permission');
			}
		}

		echo sprintf(
			'<script>window.parent.CKEDITOR.tools.callFunction(%s, %s, %s);</script>',
			qa_js(qa_get('CKEditorFuncNum')),
			qa_js($url),
			qa_js($message)
		);

		return null;
	}
}
