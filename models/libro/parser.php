<?php
/**
 * Parses multipart/form-data from a PUT request.
 *
 * @return array Parsed fields and files.
 */
function parsePutMultipart(): array {
    $rawData = file_get_contents("php://input");
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

    // Make sure this is a multipart request
    if (stripos($contentType, "multipart/form-data") === false) {
        return [];
    }

    // Extract boundary from header
    if (!preg_match('/boundary=(.*)$/', $contentType, $matches)) {
        return [];
    }
    $boundary = $matches[1];

    $results = [];
    // Split content by boundary
    $parts = preg_split("/-+$boundary/", $rawData);
    // Remove the last element which is after the final boundary
    array_pop($parts);

    foreach ($parts as $part) {
        // Skip empty parts
        if (empty(trim($part))) {
            continue;
        }
        // Separate headers from body
        $part = ltrim($part, "\r\n");
        $splitPosition = strpos($part, "\r\n\r\n");
        if ($splitPosition === false) {
            continue;
        }
        $rawHeaders = substr($part, 0, $splitPosition);
        $body = substr($part, $splitPosition + 4);
        $body = rtrim($body, "\r\n");

        // Parse headers into an associative array
        $headers = [];
        foreach (explode("\r\n", $rawHeaders) as $headerLine) {
            if (strpos($headerLine, ':') !== false) {
                list($name, $value) = explode(":", $headerLine, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }

        if (!isset($headers['content-disposition'])) {
            continue;
        }
        // Extract the field name and, if available, the filename
        preg_match('/name="([^"]+)"/', $headers['content-disposition'], $nameMatch);
        $fieldName = $nameMatch[1] ?? '';
        preg_match('/filename="([^"]+)"/', $headers['content-disposition'], $fileMatch);

        if ($fileMatch) {
            // File field
            $fileName = $fileMatch[1];
            $fileType = $headers['content-type'] ?? 'application/octet-stream';
            $tmpName = tempnam(sys_get_temp_dir(), 'PUT');
            file_put_contents($tmpName, $body);

            // Mimic PHP's $_FILES structure for this field:
            $results[$fieldName] = [
                'name'     => $fileName,
                'type'     => $fileType,
                'tmp_name' => $tmpName,
                'error'    => 0,
                'size'     => filesize($tmpName)
            ];
        } else {
            // Regular form field
            $results[$fieldName] = $body;
        }
    }
    return $results;
}