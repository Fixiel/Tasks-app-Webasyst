<?php

class tasksGitMethod extends waAPIMethod
{
    protected $method = 'POST';

    protected $data;

    public function parseData()
    {
        /**
         * @link https://gitlab.com/gitlab-org/gitlab-ce/blob/master/doc/web_hooks/web_hooks.md
         *  We used old json format and he difference from format  this document
         */
        $input = file_get_contents("php://input");
        $this->data = json_decode($input, true);
    }

    public function execute()
    {
        $this->parseData();

        if (!isset($this->data['ref'])) { // ignore empty ref
            return '';
        } else {
            $tmp = explode('/', $this->data['ref']);
            $this->data['branch'] = end($tmp);
            switch ($this->data['object_kind']) {
                case 'push':
                    if ($this->data['branch'] !== 'master') {
                        $this->handlePush($this->data['commits']);
                    }
                    break;
            }
        }
    }

    public function handlePush($commits)
    {
        if (empty($commits)) {
            return null;
        };

        $pattern = '~(?:\#(\d+)\.(\d+|new))~uis'; // #100.500 is task 500 at project 100

        wa('webasyst');
        $log_model = new waLogModel();
        $task_model = new tasksTaskModel();
        $task_log_params_model = new tasksTaskLogParamsModel();

        $commit_hashes = array();
        foreach ($commits as $c) {
            $log = $this->parseCommit($c);
            if (!empty($log['params']['git.id'])) {
                $commit_hashes[] = $log['params']['git.id'];
            }
        }

        $know_commits = $task_log_params_model->getByField(array('name' => 'git.id', 'value' => $commit_hashes), 'value');

        foreach ($commits as $c) {
            if (!preg_match_all($pattern, $c['message'], $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $match) {
                $log = $this->parseCommit($c);
                // Ignore commits that were already in the log (possibly in another branch)
                $commit_hash = ifempty($log, 'params', 'git.id', null);
                if (!empty($commit_hash) && !empty($know_commits[$commit_hash])) {
                    continue 2;
                }

                $task = null;
                list($_, $project_id, $number) = $match;
                try {
                    if ($number === 'new') {
                        $task = $task_model->add(array(
                            'name' => '',
                            'project_id' => $project_id,
                            'create_contact_id' => $log['contact_id'],
                            'assigned_contact_id' => $log['contact_id'],
                            'text' => $c['message'],
                        ));
                        $log_model->add('task_add', $task['id'], null, $log['contact_id']);
                    } else {
                        $task = $task_model->getByField(compact('project_id', 'number'));
                    }
                } catch (waException $e) {
                    //echo((string)$e);
                }

                if ($task) {
                    // add log, but not send notification
                    $log = tasksHelper::addLog($task, $log, false);
                    $log_model->add('task_action', $log['task_id'].':'.$log['id'], null, $log['contact_id']);
                }
            }
        }
    }

    protected function parseCommit($commit)
    {
        // Find contact by email
        // Set 0 contact because null contact well be replaced to current active user
        $contact_id = 0;
        $collection = new waContactsCollection(sprintf('/search/is_user=1&email=%s/', $commit['author']['email']));
        $contacts = $collection->getContacts('id', 0, 2);
        if (count($contacts) === 1) {
            $contact = reset($contacts);
            $contact_id = $contact['id'];
        }

        // Quote commit text into log
        $text = $commit['message'];
        $text = str_replace(array('@done', '@closed'), array(' @done', ' @closed'), $text);

        //If contact not found, set name and email from commit
        if ($contact_id === 0) {
            $text = _w('Author').': '.$commit['author']['name']."\n".
                    _w('Email').': '.$commit['author']['email']."\n"
                    .$text;
        }

        $text = '>'.str_replace("\n", "\n>", $text);

        return array(
            'text'       => $text,
            'action'     => 'git.commit',
            'contact_id' => $contact_id,
            'params'     => array(
                'git.id'       => $commit['id'],
                'git.branch'   => $this->data['branch'],
                'git.homepage' => $this->data['repository']['homepage'],
            ),
        );
    }
}
