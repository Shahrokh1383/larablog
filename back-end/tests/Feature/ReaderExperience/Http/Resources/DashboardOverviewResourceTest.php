<?php

use Modules\ReaderExperience\Http\Resources\DashboardOverviewResource;

test('DashboardOverviewResource formats array correctly', function () {
    $data = [
        'posts_read_count' => 5,
        'total_reading_time' => 120,
        'comments_count' => 3,
        'is_top_commenter' => true,
        'total_comments' => 10,
        'total_saved_posts' => 4,
    ];

    $resource = new DashboardOverviewResource($data);
    $array = $resource->toArray(request());

    expect($array)->toMatchArray($data);
});