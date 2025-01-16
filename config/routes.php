<?php
$routes = [
    // User Routes
    'GET /users' => 'UserController@getAllUsers',
    'GET /users/{id}' => 'UserController@getUserById',
    'POST /users' => 'UserController@createUser',
    'PUT /users/{id}' => 'UserController@updateUser',
    'DELETE /users/{id}' => 'UserController@deleteUser',
    'GET /test' => 'UserController@test',
    'GET /users/current' => 'UserController@getCurrentUser',


    // Auth Routes
    'POST /register' => 'UserController@registerUser',
    'POST /login' => 'UserController@loginUser',


    // Post Routes
    'GET /posts' => 'PostController@getAllPosts',
    'GET /posts/{id}' => 'PostController@getPostById',
    'GET /posts/{id}/comments' => 'PostController@getCommentsByPostId', 
    'POST /posts' => 'PostController@createPost',
    'PUT /posts/{id}' => 'PostController@updatePost',
    'DELETE /posts/{id}' => 'PostController@deletePost',

    // Comment Routes
    'GET /comments' => 'CommentController@getAllComments',
    'GET /comments/{id}' => 'CommentController@getCommentById',
    'POST /comments' => 'CommentController@createComment',
    'PUT /comments/{id}' => 'CommentController@updateComment',
    'DELETE /comments/{id}' => 'CommentController@deleteComment',

    // Vote Routes
    'GET /votes' => 'VoteController@getAllVotes',
    'GET /votes/{id}' => 'VoteController@getVoteById',
    'POST /votes' => 'VoteController@createVote',
    'DELETE /votes/{id}' => 'VoteController@deleteVote',

    // Notification Routes
    'GET /notifications' => 'NotificationController@getAllNotifications',
    'GET /notifications/{id}' => 'NotificationController@getNotificationById',
    'POST /notifications' => 'NotificationController@createNotification',
    'DELETE /notifications/{id}' => 'NotificationController@deleteNotification',

    // Tag Routes
    'GET /tags' => 'TagController@getAllTags',
    'POST /tags' => 'TagController@createTag',

    // PostTag Routes
    'POST /posttags' => 'PostTagController@createPostTag',

    // Category Routes
    'GET /categories' => 'CategoryController@getAllCategories',
    'POST /categories' => 'CategoryController@createCategory',
];

return $routes;
