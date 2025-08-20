<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['HTTP_USER_AGENT'], 'Bitbucket-Webhooks') > -1) {

    $contents = file_get_contents('php://input');
    $contents = json_decode($contents, true);

    if (!is_null($contents) && is_array($contents)) {

        if (isset($contents['push']['changes']) && count($contents['push']['changes']) > 0) {
            foreach($contents['push']['changes'] as $change) {
                if ($change['created']) {
                    if ($change['new']['type'] === 'tag') {
                        $new = $change['new'];

                        $data = [
                            'tag_name' => $new['name'],
                            'tag_message' => $new['message'],
                            'tag_url' => $new['links']['html']['href'],
                            'tag_created_at' => date('Y-m-d H:i:s', strtotime($new['date'])),
                            'commit_hash' => $new['target']['hash'],
                            'author' => $new['target']['author']['raw'],
                            'author_url' => $new['target']['author']['user']['links']['html']['href'],
                            'author_avatar' => $new['target']['author']['user']['links']['avatar']['href'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'status' => 'waiting' // <<<  executa automaticamente sem necessidade de autorização manual
                        ];

                        $fields = [];
                        $values = [];

                        foreach($data as $field => $value) {
                            $fields[] = $field;
                            $values[] = "'" . addslashes(strip_tags($value)) . "'";
                        }

                        $result = $sistema->DB_insert('tb_system_updates', implode(', ', $fields), implode(', ', $values));

                        if ($result->query) {
                            header($_SERVER['SERVER_PROTOCOL'] . ' 202 Accepted');
                            die(json_encode($data));
                        }

                        header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
                        die();
                    }
                }
            }
        }
    }
    die();
}

header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
die();

?>