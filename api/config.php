<?php
return array(
    'APP_DEBUG' => false,

    'DB_SERVER' => 'localhost',
    'DB_USER' => 'test',
    'DB_PWD' => 'test',
    'DB_NAME' => 'test_sports_meeting',

    'DANMAKU_TABLE_NAME' => 'sports_meeting_danmaku',
    'VOTE_DETAIL_TABLE_NAME' => 'sports_meeting_vote_detail',
    'VOTE_COUNT_TABLE_NAME' => 'sports_meeting_vote_count',

    'SESSION_NAME' => 'VOTE_PHPSESSID',

    'DANMAKU_LIMIT' => 25, //单次获取弹幕上限
    'COLLEGE_ID_MIN' => 0, //书院id下限
    'COLLEGE_ID_MAX' => 7, //书院id上限
    'VOTE_INTERVAL' => 300, //投票时间间隔（秒）
    'IS_CHECK_IP' => true, //是否检查ip防刷票
);