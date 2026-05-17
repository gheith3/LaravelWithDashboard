<?php

return [

    'auth' => [
        'register_success'     => 'Registration successful',
        'login_success'        => 'Login successful',
        'invalid_credentials'  => 'Invalid credentials or account is inactive',
        'token_refreshed'      => 'Token refreshed',
        'token_refresh_failed' => 'Token cannot be refreshed. Please login again.',
        'logout_success'       => 'Logged out successfully',
        'profile_retrieved'    => 'User profile retrieved',
    ],

    'posts' => [
        'retrieved' => 'Posts retrieved',
        'show'      => 'Post retrieved',
        'created'   => 'Post created',
        'updated'   => 'Post updated',
        'deleted'   => 'Post deleted',
    ],

    'tags' => [
        'retrieved' => 'Tags retrieved',
    ],

    'comments' => [
        'retrieved' => 'Comments retrieved',
        'submitted' => 'Comment submitted and awaiting moderation',
    ],

    'errors' => [
        'server'        => 'An unexpected error occurred. Please try again later.',
        'unauthorized'  => 'Unauthorized. Please login to continue.',
        'forbidden'     => 'You do not have permission to perform this action.',
        'not_found'     => 'The requested resource was not found.',
        'validation'    => 'The given data was invalid.',
        'token_expired' => 'Your session has expired. Please login again.',
        'token_invalid' => 'Invalid token. Please login again.',
    ],

];
