<?php

final class tasksLogItemHandler
{
    public const ACTION_ADD = 'add';
    public const ACTION_EDIT = 'edit';

    public function log(tasksTask2 $task, $beforeStatusId, $action): ?array
    {
        $logModel = new tasksTaskLogModel();
        $logItem = [
            'project_id' => $task->getProjectId(),
            'task_id' => $task->getId(),
            'action' => $action,
            'before_status_id' => $beforeStatusId,
            'after_status_id' => $task->getStatusId(),
            'assigned_contact_id' => $task->getAssignedContactId(),
        ];

        $logId = $logModel->add($logItem);

        return $logModel->getById($logId);
    }
}
