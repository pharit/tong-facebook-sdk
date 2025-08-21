<?php

namespace Tong\FacebookSdk\Services;

use Tong\FacebookSdk\Exceptions\FacebookApiException;

class FacebookGraphService
{
    protected FacebookService $facebookService;

    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    /**
     * Get user profile information
     */
    public function getUserProfile(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me', $params);
    }

    /**
     * Get user's friends
     */
    public function getUserFriends(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/friends', $params);
    }

    /**
     * Get user's posts
     */
    public function getUserPosts(string $accessToken, array $fields = [], int $limit = 25): array
    {
        $params = [
            'access_token' => $accessToken,
            'limit' => $limit,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/posts', $params);
    }

    /**
     * Create a post on user's timeline
     */
    public function createPost(string $accessToken, string $message, array $additionalData = []): array
    {
        $data = array_merge([
            'access_token' => $accessToken,
            'message' => $message,
        ], $additionalData);

        return $this->facebookService->post('me/feed', $data);
    }

    /**
     * Get user's photos
     */
    public function getUserPhotos(string $accessToken, array $fields = [], int $limit = 25): array
    {
        $params = [
            'access_token' => $accessToken,
            'limit' => $limit,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/photos', $params);
    }

    /**
     * Upload a photo
     */
    public function uploadPhoto(string $accessToken, string $imageUrl, string $message = ''): array
    {
        $data = [
            'access_token' => $accessToken,
            'url' => $imageUrl,
        ];

        if ($message) {
            $data['message'] = $message;
        }

        return $this->facebookService->post('me/photos', $data);
    }

    /**
     * Get user's albums
     */
    public function getUserAlbums(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/albums', $params);
    }

    /**
     * Create an album
     */
    public function createAlbum(string $accessToken, string $name, string $description = ''): array
    {
        $data = [
            'access_token' => $accessToken,
            'name' => $name,
        ];

        if ($description) {
            $data['description'] = $description;
        }

        return $this->facebookService->post('me/albums', $data);
    }

    /**
     * Get page information
     */
    public function getPage(string $pageId, string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get($pageId, $params);
    }

    /**
     * Get user's pages
     */
    public function getUserPages(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/accounts', $params);
    }

    /**
     * Create a post on a page
     */
    public function createPagePost(string $pageId, string $accessToken, string $message, array $additionalData = []): array
    {
        $data = array_merge([
            'access_token' => $accessToken,
            'message' => $message,
        ], $additionalData);

        return $this->facebookService->post("{$pageId}/feed", $data);
    }

    /**
     * Get group information
     */
    public function getGroup(string $groupId, string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get($groupId, $params);
    }

    /**
     * Get user's groups
     */
    public function getUserGroups(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/groups', $params);
    }

    /**
     * Create a post in a group
     */
    public function createGroupPost(string $groupId, string $accessToken, string $message, array $additionalData = []): array
    {
        $data = array_merge([
            'access_token' => $accessToken,
            'message' => $message,
        ], $additionalData);

        return $this->facebookService->post("{$groupId}/feed", $data);
    }

    /**
     * Get comments on a post
     */
    public function getComments(string $postId, string $accessToken, array $fields = [], int $limit = 25): array
    {
        $params = [
            'access_token' => $accessToken,
            'limit' => $limit,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get("{$postId}/comments", $params);
    }

    /**
     * Add a comment to a post
     */
    public function addComment(string $postId, string $accessToken, string $message): array
    {
        $data = [
            'access_token' => $accessToken,
            'message' => $message,
        ];

        return $this->facebookService->post("{$postId}/comments", $data);
    }

    /**
     * Get likes on a post
     */
    public function getLikes(string $postId, string $accessToken, array $fields = [], int $limit = 25): array
    {
        $params = [
            'access_token' => $accessToken,
            'limit' => $limit,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get("{$postId}/likes", $params);
    }

    /**
     * Like a post
     */
    public function likePost(string $postId, string $accessToken): array
    {
        $data = [
            'access_token' => $accessToken,
        ];

        return $this->facebookService->post("{$postId}/likes", $data);
    }

    /**
     * Unlike a post
     */
    public function unlikePost(string $postId, string $accessToken): array
    {
        $data = [
            'access_token' => $accessToken,
        ];

        return $this->facebookService->delete("{$postId}/likes", $data);
    }

    /**
     * Search for users, pages, groups, or events
     */
    public function search(string $query, string $type, string $accessToken, array $fields = [], int $limit = 25): array
    {
        $params = [
            'access_token' => $accessToken,
            'q' => $query,
            'type' => $type,
            'limit' => $limit,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('search', $params);
    }

    /**
     * Get insights for a page or post
     */
    public function getInsights(string $objectId, string $accessToken, array $metrics = [], string $period = 'day'): array
    {
        $params = [
            'access_token' => $accessToken,
            'period' => $period,
        ];

        if (!empty($metrics)) {
            $params['metric'] = implode(',', $metrics);
        }

        return $this->facebookService->get("{$objectId}/insights", $params);
    }

    /**
     * Get user's events
     */
    public function getUserEvents(string $accessToken, array $fields = []): array
    {
        $params = [
            'access_token' => $accessToken,
        ];

        if (!empty($fields)) {
            $params['fields'] = implode(',', $fields);
        }

        return $this->facebookService->get('me/events', $params);
    }

    /**
     * Create an event
     */
    public function createEvent(string $accessToken, string $name, string $startTime, array $additionalData = []): array
    {
        $data = array_merge([
            'access_token' => $accessToken,
            'name' => $name,
            'start_time' => $startTime,
        ], $additionalData);

        return $this->facebookService->post('me/events', $data);
    }
}
