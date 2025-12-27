<?php
/**
 * File Icon Mappings
 *
 * Maps file extensions, MIME types, and simple identifiers to FileIcon enum cases.
 * Used by FileIcon helper methods and IconHelper for dynamic icon resolution.
 */
return [
    /**
     * File extension to FileIcon enum case name mapping
     * Extensions should be lowercase without the dot
     */
    'extensions' => [
        // Documents
        'pdf' => 'PDF',
        'doc' => 'DOC',
        'docx' => 'DOCX',
        'txt' => 'TXT',

        // Spreadsheets
        'xls' => 'XLS',
        'xlsx' => 'XLSX',
        'csv' => 'CSV',

        // Presentations
        'ppt' => 'PPT',
        'pptx' => 'PPTX',

        // Images
        'jpg' => 'JPG',
        'jpeg' => 'JPG',
        'png' => 'PNG',
        'gif' => 'GIF',
        'svg' => 'SVG',
        'tiff' => 'TIFF',
        'tif' => 'TIFF',
        'webp' => 'WEBP',
        'eps' => 'EPS',

        // Media - Audio
        'mp3' => 'MP3',
        'wav' => 'WAV',

        // Media - Video
        'mp4' => 'MP4',
        'avi' => 'AVI',
        'mkv' => 'MKV',
        'mpeg' => 'MPEG',
        'mpg' => 'MPEG',

        // Archives
        'zip' => 'ZIP',
        'rar' => 'RAR',
        'dmg' => 'DMG',

        // Development
        'html' => 'HTML',
        'htm' => 'HTML',
        'css' => 'CSS',
        'js' => 'JS',
        'json' => 'JSON',
        'xml' => 'XML',
        'sql' => 'SQL',
        'java' => 'JAVA',

        // Design
        'psd' => 'PSD',
        'ai' => 'AI',
        'fig' => 'FIG',
        'aep' => 'AEP',
        'indd' => 'INDD',

        // Other
        'rss' => 'RSS',
        'exe' => 'EXE',
    ],

    /**
     * MIME type to FileIcon enum case name mapping
     */
    'mime_types' => [
        // Documents
        'application/pdf' => 'PDF',
        'application/msword' => 'DOC',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
        'text/plain' => 'TXT',

        // Spreadsheets
        'application/vnd.ms-excel' => 'XLS',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
        'text/csv' => 'CSV',

        // Presentations
        'application/vnd.ms-powerpoint' => 'PPT',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',

        // Images
        'image/jpeg' => 'JPG',
        'image/png' => 'PNG',
        'image/gif' => 'GIF',
        'image/svg+xml' => 'SVG',
        'image/tiff' => 'TIFF',
        'image/webp' => 'WEBP',
        'application/postscript' => 'EPS',

        // Media - Audio
        'audio/mpeg' => 'MP3',
        'audio/wav' => 'WAV',
        'audio/x-wav' => 'WAV',

        // Media - Video
        'video/mp4' => 'MP4',
        'video/x-msvideo' => 'AVI',
        'video/x-matroska' => 'MKV',
        'video/mpeg' => 'MPEG',

        // Archives
        'application/zip' => 'ZIP',
        'application/x-zip-compressed' => 'ZIP',
        'application/x-rar-compressed' => 'RAR',
        'application/x-apple-diskimage' => 'DMG',

        // Development
        'text/html' => 'HTML',
        'text/css' => 'CSS',
        'application/javascript' => 'JS',
        'text/javascript' => 'JS',
        'application/json' => 'JSON',
        'application/xml' => 'XML',
        'text/xml' => 'XML',
        'application/sql' => 'SQL',
        'text/x-java-source' => 'JAVA',

        // Design
        'image/vnd.adobe.photoshop' => 'PSD',
        'application/illustrator' => 'AI',
        'application/x-figma' => 'FIG',

        // Other
        'application/rss+xml' => 'RSS',
        'application/x-msdownload' => 'EXE',
    ],

    /**
     * Simple identifiers for template usage
     * Maps 'file-xxx' strings to FileIcon enum case names
     */
    'identifiers' => [
        // Documents
        'file-pdf' => 'PDF',
        'file-doc' => 'DOC',
        'file-docx' => 'DOCX',
        'file-txt' => 'TXT',

        // Spreadsheets
        'file-xls' => 'XLS',
        'file-xlsx' => 'XLSX',
        'file-csv' => 'CSV',

        // Presentations
        'file-ppt' => 'PPT',
        'file-pptx' => 'PPTX',

        // Images
        'file-jpg' => 'JPG',
        'file-jpeg' => 'JPG',
        'file-png' => 'PNG',
        'file-gif' => 'GIF',
        'file-svg' => 'SVG',
        'file-tiff' => 'TIFF',
        'file-webp' => 'WEBP',
        'file-eps' => 'EPS',

        // Media
        'file-mp3' => 'MP3',
        'file-mp4' => 'MP4',
        'file-avi' => 'AVI',
        'file-wav' => 'WAV',
        'file-mkv' => 'MKV',
        'file-mpeg' => 'MPEG',

        // Archives
        'file-zip' => 'ZIP',
        'file-rar' => 'RAR',
        'file-dmg' => 'DMG',

        // Development
        'file-html' => 'HTML',
        'file-css' => 'CSS',
        'file-js' => 'JS',
        'file-json' => 'JSON',
        'file-xml' => 'XML',
        'file-sql' => 'SQL',
        'file-java' => 'JAVA',
        'file-code' => 'CODE',

        // Design
        'file-psd' => 'PSD',
        'file-ai' => 'AI',
        'file-fig' => 'FIG',
        'file-aep' => 'AEP',
        'file-indd' => 'INDD',

        // Generic fallbacks
        'file-document' => 'DOCUMENT',
        'file-spreadsheet' => 'SPREADSHEET',
        'file-image' => 'IMAGE',
        'file-audio' => 'AUDIO',
        'file-video' => 'VIDEO_01',
        'file-folder' => 'FOLDER',
        'file-empty' => 'EMPTY',
    ],

    /**
     * Category-based fallbacks for unknown MIME types
     * Pattern matching using wildcards
     */
    'category_fallbacks' => [
        'image/*' => 'IMAGE',
        'video/*' => 'VIDEO_01',
        'audio/*' => 'AUDIO',
        'text/*' => 'DOCUMENT',
        'application/*' => 'DOCUMENT',
    ],

    /**
     * Default fallback for completely unknown types
     */
    'default_fallback' => 'EMPTY',
];
