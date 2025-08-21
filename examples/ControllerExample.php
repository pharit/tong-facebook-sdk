<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Tong\FacebookSdk\Services\FacebookService;
use Tong\FacebookSdk\Services\FacebookAuthService;
use Tong\FacebookSdk\Services\FacebookGraphService;
use Tong\FacebookSdk\Exceptions\FacebookApiException;

class FacebookController extends Controller
{
    public function __construct(
        private FacebookService $facebookService,
        private FacebookAuthService $authService,
        private FacebookGraphService $graphService
    ) {}

    /**
     * Redirect user to Facebook OAuth login
     */
    public function login(): JsonResponse
    {
        try {
            $loginUrl = $this->authService->getLoginUrl([
                'public_profile',
                'email',
                'user_posts',
                'user_photos',
                'user_friends'
            ]);

            return response()->json([
                'success' => true,
                'login_url' => $loginUrl
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authorization code not provided'
                ], 400);
            }

            // Exchange code for access token
            $tokenData = $this->authService->getAccessTokenFromCode($code);
            $accessToken = $tokenData['access_token'];

            // Get user information
            $user = $this->authService->getUser($accessToken, [
                'id', 'name', 'email', 'picture'
            ]);

            // Store user data in session or database
            session(['facebook_user' => $user]);
            session(['facebook_access_token' => $accessToken]);

            return response()->json([
                'success' => true,
                'user' => $user,
                'access_token' => $accessToken
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile information
     */
    public function profile(): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $profile = $this->graphService->getUserProfile($accessToken, [
                'id', 'name', 'email', 'picture', 'birthday', 'location'
            ]);

            return response()->json([
                'success' => true,
                'profile' => $profile
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's posts
     */
    public function posts(Request $request): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $limit = $request->get('limit', 10);
            $posts = $this->graphService->getUserPosts($accessToken, [
                'id', 'message', 'created_time', 'likes', 'comments'
            ], $limit);

            return response()->json([
                'success' => true,
                'posts' => $posts
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new post
     */
    public function createPost(Request $request): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $message = $request->get('message');
            
            if (!$message) {
                return response()->json([
                    'success' => false,
                    'error' => 'Message is required'
                ], 400);
            }

            $post = $this->graphService->createPost($accessToken, $message);

            return response()->json([
                'success' => true,
                'post' => $post
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's photos
     */
    public function photos(Request $request): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $limit = $request->get('limit', 10);
            $photos = $this->graphService->getUserPhotos($accessToken, [
                'id', 'source', 'created_time', 'likes'
            ], $limit);

            return response()->json([
                'success' => true,
                'photos' => $photos
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a photo
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $imageUrl = $request->get('image_url');
            $message = $request->get('message', '');
            
            if (!$imageUrl) {
                return response()->json([
                    'success' => false,
                    'error' => 'Image URL is required'
                ], 400);
            }

            $photo = $this->graphService->uploadPhoto($accessToken, $imageUrl, $message);

            return response()->json([
                'success' => true,
                'photo' => $photo
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's pages
     */
    public function pages(): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $pages = $this->graphService->getUserPages($accessToken, [
                'id', 'name', 'access_token', 'category'
            ]);

            return response()->json([
                'success' => true,
                'pages' => $pages
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a post on a page
     */
    public function createPagePost(Request $request): JsonResponse
    {
        try {
            $pageId = $request->get('page_id');
            $pageAccessToken = $request->get('page_access_token');
            $message = $request->get('message');
            
            if (!$pageId || !$pageAccessToken || !$message) {
                return response()->json([
                    'success' => false,
                    'error' => 'Page ID, page access token, and message are required'
                ], 400);
            }

            $post = $this->graphService->createPagePost($pageId, $pageAccessToken, $message);

            return response()->json([
                'success' => true,
                'post' => $post
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for users, pages, or groups
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $accessToken = session('facebook_access_token');
            
            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'No access token found'
                ], 401);
            }

            $query = $request->get('q');
            $type = $request->get('type', 'user');
            $limit = $request->get('limit', 10);
            
            if (!$query) {
                return response()->json([
                    'success' => false,
                    'error' => 'Search query is required'
                ], 400);
            }

            $results = $this->graphService->search($query, $type, $accessToken, [
                'id', 'name', 'picture'
            ], $limit);

            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout and clear session
     */
    public function logout(): JsonResponse
    {
        session()->forget(['facebook_user', 'facebook_access_token']);
        
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
