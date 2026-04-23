<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Release Integration
    |--------------------------------------------------------------------------
    |
    | Set these in your .env file:
    |
    |   GITHUB_TOKEN=ghp_xxxxxxxxxxxxxxxxxxxx   (fine-grained or classic PAT)
    |   GITHUB_OWNER=your-github-username
    |   GITHUB_REPO=your-repository-name
    |   APP_VERSION=1.0.0                       (current deployed version)
    |
    | The token only needs "read" access to Contents and Metadata.
    | If the repo is public you can omit GITHUB_TOKEN.
    */

    'token' => env('GITHUB_TOKEN'),
    'owner' => env('GITHUB_OWNER', ''),
    'repo'  => env('GITHUB_REPO', ''),

    /*
    | Current version of the application as deployed on this server.
    | Update this value in .env whenever you deploy a new version.
    | Format: semantic versioning — e.g. 1.0.0 / 1.2.3 / 2.0.0-beta
    */
    'current_version' => env('APP_VERSION', '1.0.0'),

    /*
    | Include pre-release versions (alpha, beta, RC) in the release feed.
    | Set to false in production to only expose stable releases to tenants.
    */
    'include_prereleases' => env('GITHUB_INCLUDE_PRERELEASES', false),

    /*
    | How many releases to keep in the database (oldest are trimmed).
    */
    'max_stored_releases' => env('GITHUB_MAX_RELEASES', 20),
];
