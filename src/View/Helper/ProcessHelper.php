<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Utility\ProcessActionService;
use Cake\View\Helper;

class ProcessHelper extends Helper
{
    /**
     * Determine the continue action for a process based on status and user type.
     *
     * @param object $process The process entity
     * @param string $userType 'candidate' or 'examiner'
     * @return array|null Action configuration array or null
     */
    public function getContinueAction(object $process, string $userType): ?array
    {
        return ProcessActionService::getContinueAction($process, $userType);
    }

    /**
     * Determines which user type needs to act based on process status.
     *
     * @param int $statusId The process status ID
     * @param int $userId The current user's ID
     * @param object $process The process entity
     * @return string Either 'examiner' or 'candidate'
     */
    public function determineUserType(int $statusId, int $userId, object $process): string
    {
        return ProcessActionService::determineUserType($statusId, $userId, $process);
    }
}
