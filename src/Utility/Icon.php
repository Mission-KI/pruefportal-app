<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * Icon Enum
 *
 * Central registry of all available icons in the application.
 * Maps semantic names to icon filenames for better developer experience.
 *
 * Usage:
 *   $this->element('atoms/icon', ['name' => Icon::USER_ADD])
 *   $this->element('atoms/icon', ['name' => Icon::CHECK_CIRCLE->value])
 *
 * @example Icon::HOME->value returns 'home-01'
 * @example Icon::USER_ADD->category() returns 'user'
 */
enum Icon: string
{
    /* === NAVIGATION === */
    case HOME = 'home-01';
    case HOME_ALT = 'home-02';
    case ARROW_UP = 'arrow-up';
    case ARROW_DOWN = 'arrow-down';
    case ARROW_LEFT = 'arrow-left';
    case ARROW_RIGHT = 'arrow-right';
    case ARROW_UP_LEFT = 'arrow-up-left';
    case ARROW_UP_RIGHT = 'arrow-up-right';
    case ARROW_DOWN_LEFT = 'arrow-down-left';
    case ARROW_DOWN_RIGHT = 'arrow-down-right';
    case CHEVRON_UP = 'chevron-up';
    case CHEVRON_DOWN = 'chevron-down';
    case CHEVRON_LEFT = 'chevron-left';
    case CHEVRON_RIGHT = 'chevron-right';
    case CHEVRON_SELECTOR = 'chevron-selector';
    case EXTERNAL_LINK = 'external-link';
    case STEP_COMPLETE = 'step-complete';
    case STEP_CURRENT = 'step-current';
    case STEP_INCOMPLETE = 'step-incomplete';

    /* === ACTIONS === */
    case CHECK = 'check';
    case CHECK_CIRCLE = 'check-circle';
    case CHECK_SQUARE = 'check-square';
    case EDIT = 'edit';
    case COPY = 'copy';
    case SAVE = 'save-01';
    case FILE_SAVE = 'file-save';
    case TRASH = 'trash-01';
    case TRASH_ALT = 'trash-03';
    case PLUS = 'plus';
    case PLUS_CIRCLE = 'plus-circle';
    case PLUS_SQUARE = 'plus-square';
    case MINUS = 'minus';
    case MINUS_CIRCLE = 'minus-circle';
    case MINUS_SQUARE = 'minus-square';
    case X = 'x';
    case X_CLOSE = 'x-close';
    case X_CIRCLE = 'x-circle';
    case X_SQUARE = 'x-square';
    case REFRESH = 'refresh';

    /* === STATUS & ALERTS === */
    case INFO_CIRCLE = 'info-circle';
    case HELP_CIRCLE = 'help-circle';
    case ALERT_TRIANGLE = 'alert-triangle';
    case ALERT_SQUARE = 'alert-square';
    case BELL = 'bell';
    case ACTIVITY = 'activity';
    case CIRCLE_EMPTY = 'circle-empty';
    case PLACEHOLDER = 'placeholder';

    /* === COMMUNICATION === */
    case MAIL = 'mail';
    case MAIL_OPEN = 'mail-open';
    case MESSAGE = 'message-text-square-02';
    case MESSAGE_PLUS = 'message-plus-square';
    case MESSAGE_ALERT = 'message-alert-square';
    case PHONE = 'phone';
    case ANNOTATION = 'annotation';

    /* === USER MANAGEMENT === */
    case USER_ADD = 'user-add';
    case USER_REMOVE = 'user-remove';
    case USER_EDIT = 'user-edit';
    case USER_CHECK = 'user-check';
    case USER_GROUP = 'user-group';

    /* === FILE & DATA === */
    case UPLOAD = 'upload-01';
    case UPLOAD_ALT = 'upload-03';
    case UPLOAD_CLOUD = 'upload-cloud-01';
    case DOWNLOAD = 'download-01';
    case DOWNLOAD_ALT = 'download-03';
    case INBOX = 'inbox-01';
    case BAR_CHART = 'bar-chart-square';
    case FILTER_FUNNEL = 'filter-funnel';
    case FILTER_LINES = 'filter-lines';

    /* === INTERFACE === */
    case SEARCH = 'search';
    case SETTINGS = 'settings-01';
    case SETTINGS_ALT = 'settings-04';
    case LOCK_LOCKED = 'lock-locked';
    case LOCK_UNLOCKED = 'lock-unlocked';
    case LOG_IN = 'log-in-01';
    case LOG_IN_ALT = 'log-in-04';
    case LOG_OUT = 'log-out-01';
    case LOG_OUT_ALT = 'log-out-04';
    case DOTS_VERTICAL = 'dots-vertical';

    /* === CONTENT & SOCIAL === */
    case HEART = 'heart';
    case THUMBS_UP = 'thumbs-up';
    case THUMBS_DOWN = 'thumbs-down';
    case LINK = 'link-01';
    case LINK_ALT = 'link-02';
    case PIN = 'pin-01';
    case PIN_ALT = 'pin-02';

    /* === TOOLS & SYSTEM === */
    case TOOL = 'tool-01';
    case TOOL_ALT = 'tool-02';

    /* === QUALITY DIMENSIONS === */
    case QD_DATA_QUALITY = 'data-quality';
    case QD_NON_DISCRIMINATION = 'non-discrimination';
    case QD_TRANSPARENCY = 'transparency';
    case QD_HUMAN_OVERSIGHT = 'human-oversight';
    case QD_RELIABILITY = 'reliability';
    case QD_AI_SECURITY = 'ai-security';

    /**
     * Get the category this icon belongs to
     *
     * @return string Category name (navigation, actions, status, etc.)
     */
    public function category(): string
    {
        return match ($this) {
            self::HOME, self::HOME_ALT,
            self::ARROW_UP, self::ARROW_DOWN, self::ARROW_LEFT, self::ARROW_RIGHT,
            self::ARROW_UP_LEFT, self::ARROW_UP_RIGHT, self::ARROW_DOWN_LEFT, self::ARROW_DOWN_RIGHT,
            self::CHEVRON_UP, self::CHEVRON_DOWN, self::CHEVRON_LEFT, self::CHEVRON_RIGHT,
            self::CHEVRON_SELECTOR, self::EXTERNAL_LINK, self::STEP_COMPLETE, self::STEP_INCOMPLETE, self::STEP_CURRENT
                => 'navigation',

            self::CHECK, self::CHECK_CIRCLE, self::CHECK_SQUARE,
            self::EDIT, self::COPY, self::SAVE, self::FILE_SAVE,
            self::TRASH, self::TRASH_ALT,
            self::PLUS, self::PLUS_CIRCLE, self::PLUS_SQUARE,
            self::MINUS, self::MINUS_CIRCLE, self::MINUS_SQUARE,
            self::X, self::X_CLOSE, self::X_CIRCLE, self::X_SQUARE,
            self::REFRESH
                => 'actions',

            self::INFO_CIRCLE, self::HELP_CIRCLE,
            self::ALERT_TRIANGLE, self::ALERT_SQUARE,
            self::BELL, self::ACTIVITY,
            self::CIRCLE_EMPTY, self::PLACEHOLDER
                => 'status',

            self::MAIL, self::MAIL_OPEN,
            self::MESSAGE, self::MESSAGE_PLUS, self::MESSAGE_ALERT,
            self::PHONE, self::ANNOTATION
                => 'communication',

            self::USER_ADD, self::USER_REMOVE, self::USER_EDIT,
            self::USER_CHECK, self::USER_GROUP
                => 'user',

            self::UPLOAD, self::UPLOAD_ALT, self::UPLOAD_CLOUD,
            self::DOWNLOAD, self::DOWNLOAD_ALT,
            self::INBOX, self::BAR_CHART,
            self::FILTER_FUNNEL, self::FILTER_LINES
                => 'file',

            self::SEARCH, self::SETTINGS, self::SETTINGS_ALT,
            self::LOCK_LOCKED, self::LOCK_UNLOCKED,
            self::LOG_IN, self::LOG_IN_ALT,
            self::LOG_OUT, self::LOG_OUT_ALT,
            self::DOTS_VERTICAL
                => 'interface',

            self::HEART, self::THUMBS_UP, self::THUMBS_DOWN,
            self::LINK, self::LINK_ALT,
            self::PIN, self::PIN_ALT
                => 'content',

            self::TOOL, self::TOOL_ALT
                => 'tools',

            self::QD_DATA_QUALITY, self::QD_NON_DISCRIMINATION, self::QD_TRANSPARENCY,
            self::QD_HUMAN_OVERSIGHT, self::QD_RELIABILITY, self::QD_AI_SECURITY
                => 'quality-dimensions',
        };
    }

    /**
     * Get a human-readable label for the icon
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::HOME => 'Home',
            self::HOME_ALT => 'Home (Alternative)',
            self::ARROW_UP => 'Arrow Up',
            self::ARROW_DOWN => 'Arrow Down',
            self::ARROW_LEFT => 'Arrow Left',
            self::ARROW_RIGHT => 'Arrow Right',
            self::ARROW_UP_LEFT => 'Arrow Up-Left',
            self::ARROW_UP_RIGHT => 'Arrow Up-Right',
            self::ARROW_DOWN_LEFT => 'Arrow Down-Left',
            self::ARROW_DOWN_RIGHT => 'Arrow Down-Right',
            self::CHEVRON_UP => 'Chevron Up',
            self::CHEVRON_DOWN => 'Chevron Down',
            self::CHEVRON_LEFT => 'Chevron Left',
            self::CHEVRON_RIGHT => 'Chevron Right',
            self::CHEVRON_SELECTOR => 'Chevron Selector',
            self::EXTERNAL_LINK => 'External Link',
            self::STEP_COMPLETE => 'Step complete',
            self::STEP_CURRENT => 'Step current',
            self::STEP_INCOMPLETE => 'Step incomplete',

            self::CHECK => 'Check',
            self::CHECK_CIRCLE => 'Check Circle',
            self::CHECK_SQUARE => 'Check Square',
            self::EDIT => 'Edit',
            self::COPY => 'Copy',
            self::SAVE => 'Save',
            self::FILE_SAVE => 'File Save',
            self::TRASH => 'Delete',
            self::TRASH_ALT => 'Delete (Alternative)',
            self::PLUS => 'Plus',
            self::PLUS_CIRCLE => 'Plus Circle',
            self::PLUS_SQUARE => 'Plus Square',
            self::MINUS => 'Minus',
            self::MINUS_CIRCLE => 'Minus Circle',
            self::MINUS_SQUARE => 'Minus Square',
            self::X => 'Close',
            self::X_CLOSE => 'Close (X)',
            self::X_CIRCLE => 'Close Circle',
            self::X_SQUARE => 'Close Square',
            self::REFRESH => 'Refresh',

            self::INFO_CIRCLE => 'Information',
            self::HELP_CIRCLE => 'Help',
            self::ALERT_TRIANGLE => 'Alert Triangle',
            self::ALERT_SQUARE => 'Alert Square',
            self::BELL => 'Notification',
            self::ACTIVITY => 'Activity',
            self::CIRCLE_EMPTY => 'Circle Empty',
            self::PLACEHOLDER => 'Placeholder',

            self::MAIL => 'Mail',
            self::MAIL_OPEN => 'Mail Open',
            self::MESSAGE => 'Message',
            self::MESSAGE_PLUS => 'New Message',
            self::MESSAGE_ALERT => 'Message Alert',
            self::PHONE => 'Phone',
            self::ANNOTATION => 'Annotation',

            self::USER_ADD => 'Add User',
            self::USER_REMOVE => 'Remove User',
            self::USER_EDIT => 'Edit User',
            self::USER_CHECK => 'Verify User',
            self::USER_GROUP => 'User Group',

            self::UPLOAD => 'Upload',
            self::UPLOAD_ALT => 'Upload (Alternative)',
            self::UPLOAD_CLOUD => 'Upload to Cloud',
            self::DOWNLOAD => 'Download',
            self::DOWNLOAD_ALT => 'Download (Alternative)',
            self::INBOX => 'Inbox',
            self::BAR_CHART => 'Bar Chart',
            self::FILTER_FUNNEL => 'Filter (Funnel)',
            self::FILTER_LINES => 'Filter (Lines)',

            self::SEARCH => 'Search',
            self::SETTINGS => 'Settings',
            self::SETTINGS_ALT => 'Settings (Alternative)',
            self::LOCK_LOCKED => 'Locked',
            self::LOCK_UNLOCKED => 'Unlocked',
            self::LOG_IN => 'Log In',
            self::LOG_IN_ALT => 'Log In (Alternative)',
            self::LOG_OUT => 'Log Out',
            self::LOG_OUT_ALT => 'Log Out (Alternative)',
            self::DOTS_VERTICAL => 'More Options',

            self::HEART => 'Heart',
            self::THUMBS_UP => 'Thumbs Up',
            self::THUMBS_DOWN => 'Thumbs Down',
            self::LINK => 'Link',
            self::LINK_ALT => 'Link (Alternative)',
            self::PIN => 'Pin',
            self::PIN_ALT => 'Pin (Alternative)',

            self::TOOL => 'Tool',
            self::TOOL_ALT => 'Tool (Alternative)',

            self::QD_DATA_QUALITY => 'Data Quality',
            self::QD_NON_DISCRIMINATION => 'Non-Discrimination',
            self::QD_TRANSPARENCY => 'Transparency',
            self::QD_HUMAN_OVERSIGHT => 'Human Oversight',
            self::QD_RELIABILITY => 'Reliability',
            self::QD_AI_SECURITY => 'AI Security',
        };
    }

    /**
     * Get all icons by category
     *
     * @return array<string, array<\App\Utility\Icon>>
     */
    public static function byCategory(): array
    {
        $grouped = [];
        foreach (self::cases() as $icon) {
            $category = $icon->category();
            $grouped[$category][] = $icon;
        }

        return $grouped;
    }

    /**
     * Get all icons in a specific category
     *
     * @param string $category
     * @return array<\App\Utility\Icon>
     */
    public static function inCategory(string $category): array
    {
        return array_filter(
            self::cases(),
            fn(Icon $icon) => $icon->category() === $category,
        );
    }

    /**
     * Check if an icon filename exists in the enum
     *
     * @param string $filename Icon filename without extension
     * @return bool
     */
    public static function exists(string $filename): bool
    {
        foreach (self::cases() as $icon) {
            if ($icon->value === $filename) {
                return true;
            }
        }

        return false;
    }

    /**
     * Try to find an icon by its filename
     *
     * @param string $filename Icon filename without extension
     * @return \App\Utility\Icon|null
     */
    public static function fromFilename(string $filename): ?Icon
    {
        foreach (self::cases() as $icon) {
            if ($icon->value === $filename) {
                return $icon;
            }
        }

        return null;
    }
}
