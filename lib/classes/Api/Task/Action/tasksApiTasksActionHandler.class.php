<?php

final class tasksApiTasksActionHandler
{
    /**
     * @throws tasksValidationException
     * @throws tasksResourceNotFoundException
     * @throws waException
     * @throws tasksException
     */
    public function action(tasksApiTasksActionRequest $actionRequest): int
    {
        $task = tsks()->getModel(tasksTask::class)
            ->getById($actionRequest->getTaskId());

        if (!$task) {
            throw new tasksResourceNotFoundException(_w('Task not found'));
        }

        switch ($actionRequest->getAction()) {
            case tasksTaskLogModel::ACTION_TYPE_FORWARD:
                return $this->forward($actionRequest, $task);

            case tasksTaskLogModel::ACTION_TYPE_RETURN:
                return $this->return($actionRequest, $task);

            case tasksTaskLogModel::ACTION_TYPE_EMPTY:
                return $this->default($actionRequest, $task);

            default:
                throw new tasksException(sprintf('Unknown action %s', $actionRequest->getAction()));
        }
    }

    /**
     * @throws waException
     * @throws tasksValidationException
     */
    private function forward(tasksApiTasksActionRequest $actionRequest, array $task): int
    {
        if (!$actionRequest->getAssignedContactId()) {
            throw new tasksValidationException('assigned_contact_id is required for forward action');
        }

        $log = tasksHelper::addLog($task, [
            'action' => $actionRequest->getAction(),
            'status_id' => (int) $actionRequest->getStatusId(),
            'assigned_contact_id' => (int) $actionRequest->getAssignedContactId(),
            'text' => (string) $actionRequest->getText(),
            'attachments_hash' => (string) $actionRequest->getFilesHash(),
        ]);

        tsks()->getModel('waLog')
            ->add('task_forward', $log['task_id'] . ':' . $log['id']);

        return (int) $log['id'];
    }

    /**
     * @throws waException
     * @throws tasksException
     */
    private function return(tasksApiTasksActionRequest $actionRequest, array $taskData): int
    {
        $statusId = (int) $actionRequest->getStatusId();
        $prevActorId = (int) $actionRequest->getAssignedContactId();

        $task = new tasksTask($taskData);
        if ($statusId != $task['return_status']['id'] || $prevActorId != $task['return_actor']['id']) {
            throw new tasksException(_w('Cannot return this task because another user has modified it.'));
        }

        $log = tasksHelper::addLog($taskData, [
            'action' => $actionRequest->getAction(),
            'status_id' => $statusId,
            'assigned_contact_id' => $prevActorId,
            'text' => (string) $actionRequest->getText(),
            'attachments_hash' => (string) $actionRequest->getFilesHash(),
        ]);

        tsks()->getModel('waLog')
            ->add('task_return', $log['task_id'] . ':' . $log['id']);

        return (int) $log['id'];
    }

    /**
     * @throws waException
     */
    private function default(tasksApiTasksActionRequest $actionRequest, array $task): int
    {
        $data = [
            'action' => tasksTaskLogModel::ACTION_TYPE_EMPTY,
            'status_id' => $actionRequest->getStatusId() ?: -1,
            'text' => (string) $actionRequest->getText(),
        ];

        $status = null;

        $statuses = tasksHelper::getStatuses();
        if (!empty($statuses[$data['status_id']])) {
            $status = $statuses[$data['status_id']];
            switch (ifempty($status['params']['assign'])) {
                case 'author':
                    $data['assigned_contact_id'] = $task['create_contact_id'];
                    break;

                case 'user':
                    $data['assigned_contact_id'] = $status['params']['assign_user'] ?? null;
                    break;

                case 'select':
                    $data['assigned_contact_id'] = $actionRequest->getAssignedContactId();

                    if (empty($status['params']['allow_clear_assign']) && empty($data['assigned_contact_id'])) {
                        unset($data['assigned_contact_id']);
                    }

                    break;
            }
        }

        $data['attachments_hash'] = (string) $actionRequest->getFilesHash();

        $log = tasksHelper::addLog($task, $data);

        /**
         * @event save_status_form
         * For developer. Subject to change
         */
        $params = [
            'data' => array_merge($data, waRequest::post()),
            'status' => $status,
        ];

        wa('tasks')->event('save_status_form', $params);

        tsks()->getModel('waLog')
            ->add('task_action', $log['task_id'] . ':' . $log['id']);

        return (int) $log['id'];
    }
}
