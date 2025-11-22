<?php
// FILE: /app/helpers/FileUpload.php

/**
 * FileUpload Class
 * Handles secure file uploads
 */
class FileUpload {
    private $uploadPath;
    private $maxSize;
    private $allowedTypes;
    private $errors = [];

    /**
     * Constructor
     */
    public function __construct() {
        $config = require __DIR__ . '/../../config/app.php';
        $this->uploadPath = $config['upload']['path'];
        $this->maxSize = $config['upload']['max_size'];
        $this->allowedTypes = $config['upload']['allowed_types'];

        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload a file
     * @param array $file $_FILES array element
     * @param string $subdir Optional subdirectory
     * @return string|false Filename on success, false on failure
     */
    public function upload($file, $subdir = '') {
        $this->errors = [];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'No file uploaded';
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'File upload error: ' . $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $maxMB = $this->maxSize / 1024 / 1024;
            $this->errors[] = "File size exceeds maximum allowed ({$maxMB}MB)";
            return false;
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Check file type
        if (!in_array($extension, $this->allowedTypes)) {
            $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes);
            return false;
        }

        // Generate unique filename
        $filename = $this->generateUniqueFilename($extension);

        // Build full path
        $targetDir = $this->uploadPath;
        if ($subdir) {
            $targetDir .= rtrim($subdir, '/') . '/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
        }

        $targetPath = $targetDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ($subdir ? rtrim($subdir, '/') . '/' : '') . $filename;
        }

        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }

    /**
     * Generate unique filename
     * @param string $extension
     * @return string
     */
    private function generateUniqueFilename($extension) {
        return uniqid('file_', true) . '_' . time() . '.' . $extension;
    }

    /**
     * Get upload error message
     * @param int $code
     * @return string
     */
    private function getUploadErrorMessage($code) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return isset($messages[$code]) ? $messages[$code] : 'Unknown upload error';
    }

    /**
     * Get errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Delete a file
     * @param string $filename
     * @return bool
     */
    public function delete($filename) {
        $path = $this->uploadPath . $filename;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Validate CSV file structure
     * @param string $filepath
     * @param array $requiredColumns
     * @return array|false Array of data on success, false on failure
     */
    public function parseCSV($filepath, $requiredColumns = []) {
        if (!file_exists($filepath)) {
            $this->errors[] = 'File not found';
            return false;
        }

        $data = [];
        $handle = fopen($filepath, 'r');

        if ($handle === false) {
            $this->errors[] = 'Failed to open file';
            return false;
        }

        // Read header row
        $headers = fgetcsv($handle);
        if ($headers === false) {
            $this->errors[] = 'Failed to read CSV headers';
            fclose($handle);
            return false;
        }

        // Check required columns
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headers)) {
                $this->errors[] = "Missing required column: {$column}";
                fclose($handle);
                return false;
            }
        }

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }
}
