<?php

class qa_matheditor {
	
	var $urltoroot;
	var $config;
	
	function load_module($directory, $urltoroot)
    {
		$this->urltoroot=$urltoroot;
	}
	
	function calc_quality($content, $format)
    {
		if ($format === 'html') {
            return 1.0;
        }

        return 0.8;
	}

	// Duplicate from /qa-plugin/wysiwyg-editor/qa-wysiwyg-editor.php
    // it was necessary to do it in order to make matheditor upload independent from another plugins
    public function admin_form(&$qa_content)
    {
        require_once QA_INCLUDE_DIR . 'app/upload.php';

        $saved = false;

        if (qa_clicked('wysiwyg_editor_save_button')) {
            qa_opt('wysiwyg_editor_upload_images', (int)qa_post_text('wysiwyg_editor_upload_images_field'));
            qa_opt('wysiwyg_editor_upload_all', (int)qa_post_text('wysiwyg_editor_upload_all_field'));
            qa_opt('wysiwyg_editor_upload_max_size', min(qa_get_max_upload_size(), 1048576 * (float)qa_post_text('wysiwyg_editor_upload_max_size_field')));
            $saved = true;
        }

        qa_set_display_rules($qa_content, [
            'wysiwyg_editor_upload_all_display' => 'wysiwyg_editor_upload_images_field',
            'wysiwyg_editor_upload_max_size_display' => 'wysiwyg_editor_upload_images_field',
        ]);

        return [
            'ok' => $saved ? qa_lang_html('admin/options_saved') : null,

            'fields' => [
                [
                    'label' => qa_lang_html('wysiwyg-matheditor/allow_images'),
                    'type' => 'checkbox',
                    'value' => (int)qa_opt('wysiwyg_editor_upload_images'),
                    'tags' => 'name="wysiwyg_editor_upload_images_field" id="wysiwyg_editor_upload_images_field"',
                ],

                [
                    'id' => 'wysiwyg_editor_upload_max_size_display',
                    'label' => qa_lang_html('wysiwyg-matheditor/maximum_size'),
                    'suffix' => qa_lang_html_sub('wysiwyg-matheditor/mb_max_x', qa_html(number_format($this->bytes_to_mega(qa_get_max_upload_size()), 1))),
                    'type' => 'number',
                    'value' => qa_html(number_format($this->bytes_to_mega(qa_opt('wysiwyg_editor_upload_max_size')), 1)),
                    'tags' => 'name="wysiwyg_editor_upload_max_size_field"',
                ],
            ],

            'buttons' => [
                [
                    'label' => qa_lang_html('main/save_button'),
                    'tags' => 'name="wysiwyg_editor_save_button"',
                ],
            ],
        ];
    }
	
	function get_field(&$qa_content, $content, $format, $fieldname, $rows /* $autofocus parameter deprecated */)
    {
		$scriptsrc = $this->urltoroot.'ckeditor.js?'.QA_VERSION;
		$alreadyadded = false;

		if (isset($qa_content['script_src'])) {
            foreach ($qa_content['script_src'] as $testscriptsrc) {
                if ($testscriptsrc === $scriptsrc) {
                    $alreadyadded = true;
                }
            }
        }
				
		if (!$alreadyadded) {
            $uploadimages = qa_opt('wysiwyg_editor_upload_images');
            $imageUploadUrl = qa_js(qa_path('wysiwyg-editor-upload', ['qa_only_image' => true]));
            $fileUploadUrl = qa_js(qa_path('wysiwyg-editor-upload'));

            $qa_content['script_src'][] = $scriptsrc;
            $qa_content['script_lines'][] = [
                'var qa_wysiwyg_editor_config = {',

                // File uploads
                $uploadimages ? "	filebrowserImageUploadUrl: {$imageUploadUrl}," : '',
                "	filebrowserUploadMethod: 'form',", // Use form upload instead of XHR
                "	defaultLanguage: 'en',",
                '	language: ' . qa_js(qa_opt('site_language')) . '',

                '};',
            ];
		}

        $html = $format === 'html' ? $content : qa_html($content, true);
		
		return [
			'tags' => 'name="'.$fieldname.'"',
			'value' => qa_html($html),
			'rows' => $rows,
            'html_prefix' => '<input name="'.$fieldname.'_ckeditor_ok" id="'.$fieldname.'_ckeditor_ok" type="hidden" value="0"><input name="'.$fieldname.'_ckeditor_data" id="'.$fieldname.'_ckeditor_data" type="hidden" value="'.qa_html($html).'">',
		];
	}

	function load_script($fieldname)
    {
        return
            "if (qa_ckeditor_{$fieldname} = CKEDITOR.replace(".qa_js($fieldname).", qa_wysiwyg_editor_config)) { " .
                "qa_ckeditor_{$fieldname}.setData(document.getElementById(".qa_js($fieldname.'_ckeditor_data').").value); " .
                "document.getElementById(".qa_js($fieldname.'_ckeditor_ok').").value = 1; " .
            "}";
	}
	
	function focus_script($fieldname)
    {
		return "qa_matheditor_".$fieldname.".focus();";
	}
	
	function update_script($fieldname)
    {
		return "qa_matheditor_".$fieldname.".updateElement();";
	}
	
	function read_post($fieldname)
    {
		$html=qa_post_text($fieldname);
		
		$htmlformatting=preg_replace('/<\s*\/?\s*(br|p)\s*\/?\s*>/i', '', $html); // remove <p>, <br>, etc... since those are OK in text
		
		if (preg_match('/<.+>/', $htmlformatting)) // if still some other tags, it's worth keeping in HTML
			return [
				'format' => 'html',
				'content' => qa_sanitize_html($html, false, true), // qa_sanitize_html() is ESSENTIAL for security
			];
		
		else { // convert to text
			$viewer=qa_load_module('viewer', '');

			return [
				'format' => '',
				'content' => $viewer->get_text($html, 'html', [])
			];
		}
	}

    private function bytes_to_mega($bytes)
    {
        return $bytes / 1048576;
    }
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
