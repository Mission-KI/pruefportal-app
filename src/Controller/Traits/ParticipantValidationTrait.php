<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Http\Exception\BadRequestException;

/**
 * Trait for validating and processing candidate/examiner participant data.
 *
 * This trait provides shared functionality for ProcessesController and ProjectsController
 * to handle the creation of candidate and examiner users from form data.
 */
trait ParticipantValidationTrait
{
    /**
     * Validate and process candidate/examiner user data from request.
     *
     * Handles the conversion of email/name fields into user IDs by either
     * finding existing users or creating invitations for new users.
     * Supports multiple examiners through the additional_participants field.
     *
     * @param array $requestData Request data containing participant information.
     * @param string $userFullName Full name of the user making the request (for invitation subject).
     * @param string $processTitle Title of the process (for invitation subject).
     * @return array Modified request data with user IDs instead of email/name fields.
     */
    protected function validateCandidateExaminerUser(array $requestData, string $userFullName, string $processTitle): array
    {
        $userModel = $this->fetchTable('Users');

        // Handle candidate from candidate_email/name fields
        if (isset($requestData['candidate_email']) && $requestData['candidate_email'] !== '') {
            $subject = __('Invite Candidate user Subject from Project Owner: {0} for Project: {1}', $userFullName, $processTitle);
            $requestData['candidate_user'] = $userModel->getCandidateExaminerUserId(
                $requestData['candidate_email'],
                $requestData['candidate_name'],
                $subject,
            );
            unset($requestData['candidate_email'], $requestData['candidate_name']);
        }

        // Collect all examiner IDs
        $examinerIds = [];

        // Handle first examiner from examiner_email/name fields
        if (isset($requestData['examiner_email']) && $requestData['examiner_email'] !== '') {
            $subject = __('Invite Examiner user Subject from Project Owner: {0} for Project: {1}', $userFullName, $processTitle);
            $examinerUserId = $userModel->getCandidateExaminerUserId(
                $requestData['examiner_email'],
                $requestData['examiner_name'],
                $subject,
            );
            $examinerIds[] = $examinerUserId;
            unset($requestData['examiner_email'], $requestData['examiner_name']);
        }

        // Handle additional participants (examiners only)
        if (isset($requestData['additional_participants']) && is_array($requestData['additional_participants'])) {
            foreach ($requestData['additional_participants'] as $participant) {
                if (
                    isset($participant['role']) && $participant['role'] === 'examiner' &&
                    isset($participant['email']) && $participant['email'] !== ''
                ) {
                    $subject = __('Invite Examiner user Subject from Project Owner: {0} for Project: {1}', $userFullName, $processTitle);
                    $examinerUserId = $userModel->getCandidateExaminerUserId(
                        $participant['email'],
                        $participant['name'] ?? '',
                        $subject,
                    );
                    $examinerIds[] = $examinerUserId;
                }
            }
            unset($requestData['additional_participants']);
        }

        // Set examiners using _ids format for belongsToMany association
        if (!empty($examinerIds)) {
            $requestData['examiners'] = ['_ids' => array_unique($examinerIds)];
        }

        return $requestData;
    }

    /**
     * Validates that the candidate in the request data is the currently authenticated user.
     *
     * @param array $requestData Request data containing candidate_email field.
     * @throws \Cake\Http\Exception\BadRequestException If candidate_email does not match authenticated user's email.
     * @return void
     */
    protected function validateCandidateIsCurrentUser(array $requestData): void
    {
        $user = $this->request->getAttribute('identity');

        if ($user === null) {
            throw new BadRequestException(__('User must be authenticated.'));
        }

        if (!isset($requestData['candidate_email']) || $requestData['candidate_email'] === '') {
            throw new BadRequestException(__('Candidate email is required.'));
        }

        if ($requestData['candidate_email'] !== $user->username) {
            throw new BadRequestException(__('Candidate must be the authenticated user.'));
        }
    }
}
