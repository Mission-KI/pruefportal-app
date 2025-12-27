<?php
declare(strict_types=1);

namespace App\Utility;

class ProcessActionService
{
    /**
     * Determine the continue action for a process based on status and user type.
     *
     * Returns the action configuration for primary button, or null if
     * user should not act at this status.
     *
     * @param object $process The process entity
     * @param string $userType 'candidate' or 'examiner'
     * @return array|null Action configuration array or null
     */
    public static function getContinueAction(object $process, string $userType): ?array
    {
        $baseAction = [
            'variant' => 'primary',
            'size' => 'md',
        ];

        if ($userType === 'candidate') {
            switch ($process->status_id) {
                case 0:
                    return array_merge($baseAction, [
                        'label' => __('Prüfung starten'),
                        'url' => ['controller' => 'Processes', 'action' => 'start', $process->id],
                    ]);

                case 10:
                    $url = $process->hasValue('usecase_descriptions')
                        ? ['controller' => 'UsecaseDescriptions', 'action' => 'edit', $process->usecase_descriptions[0]->id]
                        : ['controller' => 'UsecaseDescriptions', 'action' => 'add', $process->id];

                    return array_merge($baseAction, [
                        'label' => __('Prüfung fortsetzen'),
                        'url' => $url,
                    ]);

                case 20:
                    return array_merge($baseAction, [
                        'label' => __('Prüfung fortsetzen'),
                        'url' => ['controller' => 'Criteria', 'action' => 'index', $process->id],
                    ]);

                case 30:
                    return array_merge($baseAction, [
                        'label' => __('Prüfung fortsetzen'),
                        'url' => ['controller' => 'Indicators', 'action' => 'index', $process->id],
                    ]);

                case 35:
                    return array_merge($baseAction, [
                        'label' => __('Prüfung fortsetzen'),
                        'url' => ['controller' => 'Indicators', 'action' => 'decideValidation', $process->id],
                    ]);

                case 40:
                    return null;

                case 50:
                    return array_merge($baseAction, [
                        'label' => __('Prüfung fortsetzen'),
                        'url' => ['controller' => 'Indicators', 'action' => 'acceptValidation', $process->id],
                    ]);

                case 60:
                    return array_merge($baseAction, [
                        'label' => __('Prüfergebnis'),
                        'url' => ['controller' => 'Processes', 'action' => 'totalResult', $process->id],
                    ]);

                default:
                    return null;
            }
        }

        if ($userType === 'examiner') {
            switch ($process->status_id) {
                case 15:
                    // Examiner needs to review the UCD
                    $ucdId = null;
                    if ($process->hasValue('usecase_descriptions') && !empty($process->usecase_descriptions)) {
                        $ucdId = $process->usecase_descriptions[0]->id;
                    }
                    if ($ucdId) {
                        return array_merge($baseAction, [
                            'label' => __('Review UCD'),
                            'url' => ['controller' => 'UsecaseDescriptions', 'action' => 'review', $ucdId],
                        ]);
                    }

                    return null;

                case 40:
                    return array_merge($baseAction, [
                        'label' => __('Prüfung fortsetzen'),
                        'url' => ['controller' => 'Indicators', 'action' => 'validation', $process->id],
                    ]);

                case 60:
                    return array_merge($baseAction, [
                        'label' => __('Prüfergebnis'),
                        'url' => ['controller' => 'Processes', 'action' => 'totalResult', $process->id],
                    ]);

                default:
                    return null;
            }
        }

        return null;
    }

    /**
     * Determines which user type needs to act based on process status.
     *
     * @param int $statusId The process status ID
     * @param int $userId The current user's ID
     * @param object $process The process entity
     * @return string Either 'examiner' or 'candidate'
     */
    public static function determineUserType(int $statusId, int $userId, object $process): string
    {
        $isExaminer = $process->isUserExaminer($userId);

        if ($statusId === 60 || $statusId === 40) {
            return $isExaminer ? 'examiner' : 'candidate';
        }

        if (in_array($statusId, [10, 20, 30, 35, 50])) {
            return 'candidate';
        }

        return 'candidate';
    }
}
