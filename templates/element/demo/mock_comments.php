<?php
/**
 * Mock Comment Data Helper for UI Demo
 *
 * Converts JSON data structures into mock comment entities.
 * Keeps index.php simple by handling entity creation here.
 */

/**
 * Create a mock user entity from data
 *
 * @param string $fullName User's full name
 * @return \stdClass Mock user object
 */
function createMockUser(string $fullName): \stdClass
{
    $mockUser = new \stdClass();
    $mockUser->full_name = $fullName;
    return $mockUser;
}

/**
 * Create a mock comment entity from data
 *
 * @param array $data Comment data from JSON
 * @param array $childComments Array of child comment entities
 * @return \stdClass Mock comment object
 */
function createMockComment(array $data, array $childComments = []): \stdClass
{
    $mockComment = new \stdClass();
    $mockComment->content = $data['content'];
    $mockComment->reference_id = $data['reference_id'];
    $mockComment->created = new \Cake\I18n\DateTime($data['created']);
    $mockComment->user = createMockUser($data['user_full_name']);
    $mockComment->child_comments = $childComments;
    $mockComment->is_new = $data['is_new'] ?? false;
    return $mockComment;
}

/**
 * Create a mock process entity from data
 *
 * @param array $data Process data from JSON
 * @return \stdClass Mock process object
 */
function createMockProcess(array $data): \stdClass
{
    $mockProcess = new \stdClass();
    $mockProcess->id = $data['id'];
    $mockProcess->title = $data['title'];
    return $mockProcess;
}

/**
 * Build comment thread from JSON data
 *
 * @param array $data Mock thread data from JSON
 * @return array Array of mock comment entities
 */
function buildMockCommentThread(array $data): array
{
    $replyComment = createMockComment($data['reply_comment']);
    $topComment = createMockComment($data['top_comment'], [$replyComment]);
    return [$topComment];
}
