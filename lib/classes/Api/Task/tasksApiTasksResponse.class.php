<?php

final class tasksApiTasksResponse implements tasksApiResponseInterface
{
    /**
     * @var array<tasksApiTaskDto>
     */
    private $tasks = [];

    /**
     * @var int
     */
    private $total_count;

    /**
     * tasksApiTasksResponse constructor.
     *
     * @param array $tasks
     * @param int   $total_count
     */
    public function __construct(array $tasks, int $total_count)
    {
        foreach ($tasks as $taskDatum) {
            $this->tasks[] = tasksApiTaskDtoFactory::create(new tasksTask($taskDatum));
        }
        $this->total_count = $total_count;
    }

    public function getStatus(): int
    {
        return self::HTTP_OK;
    }

    public function getResponseBody(): array
    {
        return [
            'total_count' => $this->total_count,
            'data' => $this->tasks,
        ];
    }
}
