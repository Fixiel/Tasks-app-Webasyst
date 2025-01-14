<?php

class tasksApiMilestoneDtoFactory
{
    public static function fromEntity(tasksMilestone $milestone, tasksApiCountsDto $countsDto): tasksApiMilestoneDto
    {
        $daysLeft = null;
        $text = null;
        $color = null;
        $dueDate = null;
        if ($milestone->getDueDate()) {
            $dueDate = $milestone->getDueDate()->format('Y-m-d');
            $daysLeft = tasksHelper::calcDatesDiffInDays($dueDate, 'today');
            $text = tasksHelper::formatDueText($daysLeft);
            $color = tasksHelper::formatDueColor($daysLeft);
        }

        return new tasksApiMilestoneDto(
            $milestone->getId(),
            $milestone->getName(),
            $milestone->getProjectId(),
            $milestone->getDescription(),
            $dueDate,
            $milestone->isClosed(),
            $daysLeft,
            $text,
            $color,
            $countsDto
        );
    }

    public static function fromArray(array $data, tasksApiCountsDto $countsDto): tasksApiMilestoneDto
    {
        return new tasksApiMilestoneDto(
            (int) $data['id'],
            $data['name'] ?? '',
            (int) $data['project_id'],
            $data['description'] ?? '',
            !empty($data['due_date']) ? $data['due_date'] : null,
            isset($data['closed']) ? filter_var($data['closed'], FILTER_VALIDATE_BOOLEAN) : false,
            (int) $data['days_left'],
            $data['view']['due_text'] ?? null,
            $data['due_color_class'] ?? null,
            $countsDto
        );
    }
}
