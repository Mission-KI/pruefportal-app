<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Configure;

/**
 * FileIcon Enum
 *
 * Registry of file type icons for documents, images, media, etc.
 * Maps semantic names to icon filenames in webroot/icons/file-icons/.
 *
 * Usage:
 *   $this->element('atoms/icon', ['name' => FileIcon::PDF])
 *   $this->element('atoms/icon', ['name' => 'file-pdf'])
 *   FileIcon::fromExtension('pdf')
 *   FileIcon::fromMimeType('application/pdf')
 *
 * @example FileIcon::PDF->value returns 'PDF, Type=Default'
 * @example FileIcon::PDF->category() returns 'documents'
 */
enum FileIcon: string
{
    /* === DOCUMENTS === */
    case DOC = 'DOC, Type=Default';
    case DOCX = 'DOCX, Type=Default';
    case PDF = 'PDF, Type=Default';
    case TXT = 'TXT, Type=Default';

    /* === SPREADSHEETS === */
    case XLS = 'XLS, Type=Default';
    case XLSX = 'XLSX, Type=Default';
    case CSV = 'CSV, Type=Default';

    /* === PRESENTATIONS === */
    case PPT = 'PPT, Type=Default';
    case PPTX = 'PPTX, Type=Default';

    /* === IMAGES === */
    case JPG = 'JPG, Type=Default';
    case JPEG = 'JPEG, Type=Default';
    case PNG = 'PNG, Type=Default';
    case GIF = 'GIF, Type=Default';
    case SVG = 'SVG, Type=Default';
    case TIFF = 'TIFF, Type=Default';
    case WEBP = 'WebP, Type=Default';
    case IMG = 'IMG, Type=Default';
    case EPS = 'EPS, Type=Default';

    /* === MEDIA === */
    case MP3 = 'MP3, Type=Default';
    case MP4 = 'MP4, Type=Default';
    case AVI = 'AVI, Type=Default';
    case WAV = 'WAV, Type=Default';
    case MKV = 'MKV, Type=Default';
    case MPEG = 'MPEG, Type=Default';

    /* === ARCHIVES === */
    case ZIP = 'ZIP, Type=Default';
    case RAR = 'RAR, Type=Default';
    case DMG = 'DMG, Type=Default';

    /* === DEVELOPMENT === */
    case HTML = 'HTML, Type=Default';
    case CSS = 'CSS, Type=Default';
    case JS = 'JS, Type=Default';
    case JSON = 'JSON, Type=Default';
    case XML = 'XML, Type=Default';
    case SQL = 'SQL, Type=Default';
    case JAVA = 'JAVA, Type=Default';
    case CODE = 'Code, Type=Solid';

    /* === DESIGN === */
    case PSD = 'PSD (Photoshop), Type=Default';
    case AI = 'AI (Illustrator), Type=Default';
    case FIG = 'FIG (Figma), Type=Default';
    case AEP = 'AEP (After Effects), Type=Default';
    case INDD = 'INDD (InDesign), Type=Default';

    /* === OTHER === */
    case RSS = 'RSS, Type=Default';
    case EXE = 'EXE, Type=Default';

    /* === GENERIC FALLBACKS === */
    case DOCUMENT = 'Document, Type=Default';
    case SPREADSHEET = 'Spreadsheet, Type=Default';
    case IMAGE = 'Image, Type=Default';
    case AUDIO = 'Audio, Type=Default';
    case VIDEO_01 = 'Video 01, Type=Default';
    case VIDEO_02 = 'Video 02, Type=Default';
    case FOLDER = 'Folder, Type=Default';
    case EMPTY = 'Empty, Type=Default';

    /**
     * Get the category this icon belongs to
     *
     * @return string Category name (documents, images, media, etc.)
     */
    public function category(): string
    {
        return match ($this) {
            self::DOC, self::DOCX, self::PDF, self::TXT, self::DOCUMENT
                => 'documents',

            self::XLS, self::XLSX, self::CSV, self::SPREADSHEET
                => 'spreadsheets',

            self::PPT, self::PPTX
                => 'presentations',

            self::JPG, self::JPEG, self::PNG, self::GIF, self::SVG,
            self::TIFF, self::WEBP, self::IMG, self::EPS, self::IMAGE
                => 'images',

            self::MP3, self::MP4, self::AVI, self::WAV, self::MKV,
            self::MPEG, self::AUDIO, self::VIDEO_01, self::VIDEO_02
                => 'media',

            self::ZIP, self::RAR, self::DMG
                => 'archives',

            self::HTML, self::CSS, self::JS, self::JSON,
            self::XML, self::SQL, self::JAVA, self::CODE
                => 'development',

            self::PSD, self::AI, self::FIG, self::AEP, self::INDD
                => 'design',

            self::RSS, self::EXE, self::FOLDER, self::EMPTY
                => 'other',
        };
    }

    /**
     * Get file icon from file extension
     *
     * @param string $extension File extension (without dot)
     * @return self FileIcon (uses fallback if not found)
     */
    public static function fromExtension(string $extension): self
    {
        $mappings = require CONFIG . 'file_icon_mappings.php';
        $extension = strtolower(trim($extension, '.'));

        if (isset($mappings['extensions'][$extension])) {
            $caseName = $mappings['extensions'][$extension];
            // Use constant() to get enum case by name
            $enumCase = constant('self::' . $caseName);
            if ($enumCase instanceof self) {
                return $enumCase;
            }
        }

        return self::getFallback('Unknown file extension', $extension);
    }

    /**
     * Get file icon from MIME type
     *
     * @param string $mimeType MIME type (e.g., 'application/pdf')
     * @return self FileIcon (uses fallback if not found)
     */
    public static function fromMimeType(string $mimeType): self
    {
        $mappings = require CONFIG . 'file_icon_mappings.php';
        $mimeType = strtolower(trim($mimeType));

        // Try exact match first
        if (isset($mappings['mime_types'][$mimeType])) {
            $caseName = $mappings['mime_types'][$mimeType];
            $enumCase = constant('self::' . $caseName);
            if ($enumCase instanceof self) {
                return $enumCase;
            }
        }

        // Try category fallback (e.g., 'image/*' for 'image/unknown')
        foreach ($mappings['category_fallbacks'] as $pattern => $fallbackCase) {
            $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
            if (preg_match($regex, $mimeType)) {
                $enumCase = constant('self::' . $fallbackCase);
                if ($enumCase instanceof self) {
                    return $enumCase;
                }
            }
        }

        return self::getFallback('Unknown MIME type', $mimeType);
    }

    /**
     * Get file icon from filename (extracts extension)
     *
     * @param string $filename Filename with extension
     * @return self FileIcon (uses fallback if no extension)
     */
    public static function fromFilename(string $filename): self
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($extension)) {
            return self::getFallback('No file extension found', $filename);
        }

        return self::fromExtension(strtolower($extension));
    }

    /**
     * Get fallback icon with optional console warning
     *
     * @param string $reason Reason for fallback (for logging)
     * @param string|null $value The unmapped value
     * @return self Fallback FileIcon
     */
    protected static function getFallback(string $reason, ?string $value = null): self
    {
        $mappings = require CONFIG . 'file_icon_mappings.php';
        $fallbackCase = $mappings['default_fallback'];

        // Log warning in debug mode
        if (Configure::read('debug')) {
            $message = "FileIcon fallback: {$reason}";
            if ($value !== null) {
                $message .= " (value: {$value})";
            }
            trigger_error($message, E_USER_WARNING);
        }

        $enumCase = constant('self::' . $fallbackCase);

        return $enumCase instanceof self ? $enumCase : self::DOCUMENT;
    }
}
