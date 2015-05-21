<?php
/**
 * upload.php
 *
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 *
 * Modified by Rocketgenius
 */

class GFAsyncUpload {

    public static function upload() {
        GFCommon::log_debug("GFAsyncUpload::upload() - Starting");
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        send_nosniff_header();
        nocache_headers();
        status_header(200);

        // If the file is bigger than the server can accept then the form_id might not arrive.
        // This might happen if the file is bigger than the max post size ini setting.
        // Validation in the browser reduces the risk of this happening.
        if (!isset($_REQUEST["form_id"])) {
            GFCommon::log_debug("GFAsyncUpload::upload() - File upload aborted because the form_id was not found. The file may have been bigger than the max post size ini setting.");
            die('{"status" : "error", "error" : {"code": 500, "message": "' . __("Failed to upload file.", "gravityforms") . '"}}');
        }

        $form_id        = $_REQUEST["form_id"];
        $form_unique_id = rgpost("gform_unique_id");
        $form           = GFFormsModel::get_form_meta($form_id);

        $target_dir = GFFormsModel::get_upload_path($form_id) . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;

        wp_mkdir_p($target_dir);

        $cleanup_target_dir = true; // Remove old files
        $maxFileAge         = 5 * 3600; // Temp file age in seconds

        // Chunking is not currently implemented in the front-end because it's not widely supported. The code is left here for when browsers catch up.
        $chunk  = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $file_name = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        $field_id  = rgpost("field_id");
        $field     = GFFormsModel::get_field($form, $field_id);
        // Clean the fileName for security reasons
        $file_name = preg_replace('/[^\w\._]+/', '_', $file_name);

        $ext_pos   = strrpos($file_name, '.');
        $extension = strtolower(substr($file_name, $ext_pos + 1));

        $allowed_extensions    = isset($field["allowedExtensions"]) && !empty($field["allowedExtensions"]) ? GFCommon::clean_extensions(explode(",", strtolower($field["allowedExtensions"]))) : array();
        $disallowed_extensions = GFCommon::get_disallowed_file_extensions();

        if (empty($field["allowedExtensions"]) && in_array($extension, $disallowed_extensions)) {
            GFCommon::log_debug("GFAsyncUpload::upload() - illegal file extension: {$file_name})");
            die('{"status" : "error", "error" : {"code": 104, "message": "' . __("The uploaded file type is not allowed.", "gravityforms") . '"}}');
        } elseif (!empty($allowed_extensions) && !in_array($extension, $allowed_extensions)) {
            GFCommon::log_debug("GFAsyncUpload::upload() - The uploaded file type is not allowed: {$file_name})");
            die('{"status" : "error", "error" : {"code": 104, "message": "' . sprintf(__("The uploaded file type is not allowed. Must be one of the following: %s", "gravityforms"), strtolower($field["allowedExtensions"])) . '"}}');
        }

        $tmp_file_name = $form_unique_id . "_input_" . $field_id . "_" . $file_name;

        $file_path = $target_dir . $tmp_file_name;

        // Remove old temp files
        if ($cleanup_target_dir) {
            if (is_dir($target_dir) && ($dir = opendir($target_dir))) {
                while (($file = readdir($dir)) !== false) {
                    $tmp_file_path = $target_dir . $file;

                    // Remove temp file if it is older than the max age and is not the current file
                    if (preg_match('/\.part$/', $file) && (filemtime($tmp_file_path) < time() - $maxFileAge) && ($tmp_file_path != "{$file_path}.part")) {
                        GFCommon::log_debug("GFAsyncUpload::upload() - Deleting file: " . $tmp_file_path);
                        @unlink($tmp_file_path);
                    }
                }
                closedir($dir);
            } else {
                GFCommon::log_debug("GFAsyncUpload::upload() - Failed to open temp directory: " . $target_dir);
                die('{"status" : "error", "error" : {"code": 100, "message": "' . __("Failed to open temp directory.", "gravityforms") . '"}}');
            }
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES["file"]['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = @fopen($_FILES["file"]['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else {
                        die('{"status" : "error", "error" : {"code": 101, "message": "' . __("Failed to open input stream.", "gravityforms") . '"}}');
                    }

                    @fclose($in);
                    @fclose($out);
                    @unlink($_FILES["file"]['tmp_name']);
                } else {
                    die('{"status" : "error", "error" : {"code": 102, "message": "' . __("Failed to open output stream.", "gravityforms") . '"}}');
                }

            } else {
                die('{"status" : "error", "error" : {"code": 103, "message": "' . __("Failed to move uploaded file.", "gravityforms") . '"}}');
            }

        } else {
            // Open temp file
            $out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = @fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else {
                    die('{"status" : "error", "error" : {"code": 101, "message": "' . __("Failed to open input stream.", "gravityforms") . '"}}');
                }

                @fclose($in);
                @fclose($out);
            } else {
                die('{"status" : "error", "error" : {"code": 102, "message": "' . __("Failed to open output stream.", "gravityforms") . '"}}');
            }

        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$file_path}.part", $file_path);
        }

        $uploaded_filename = $_FILES["file"]["name"];

        $output = '{"status" : "ok", "data" : {"temp_filename" : "' . $tmp_file_name . '", "uploaded_filename" : "' . $uploaded_filename . '"}}';

        GFCommon::log_debug(sprintf("GFAsyncUpload::upload() - File upload complete. temp_filename: %s  uploaded_filename: %s ", $tmp_file_name, $uploaded_filename));

        die($output);
    }

}

GFAsyncUpload::upload();
