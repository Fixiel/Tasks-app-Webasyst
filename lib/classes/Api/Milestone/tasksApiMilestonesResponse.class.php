<?php

final class tasksApiMilestonesResponse implements tasksApiResponseInterface
{
    /**
     * @var array<tasksApiMilestoneDto>
     */
    private $milestones = [];

    /**
     * tasksApiMilestonesResponse constructor.
     *
     * @param array $milestones
     */
    public function __construct(array $milestones)
    {
        $counts = tasksApiCountsDtoFactory::createForMilestones();

        foreach ($milestones as $milestone) {
            $this->milestones[] = tasksApiMilestoneDtoFactory::fromArray(
                $milestone,
                $counts[$milestone['id']] ?? tasksApiCountsDtoFactory::createEmpty()
            );
        }
    }

    public function getStatus(): int
    {
        return self::HTTP_OK;
    }

    public function getResponseBody(): array
    {
        return $this->milestones;
    }
}
