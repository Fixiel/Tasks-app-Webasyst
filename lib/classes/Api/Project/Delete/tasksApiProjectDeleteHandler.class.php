<?php

final class tasksApiProjectDeleteHandler
{
    /**
     * @param tasksApiProjectDeleteRequest $deleteRequest
     *
     * @return bool
     * @throws tasksException
     * @throws waException
     */
    public function delete(tasksApiProjectDeleteRequest $deleteRequest): bool
    {
        if (!tsks()->getRightResolver()->contactCanAddProject(wa()->getUser())) {
            throw new tasksException('Project not found', 404);
        }

        /** @var tasksProject $project */
        $project = tsks()->getEntityRepository(tasksProject::class)->findById($deleteRequest->getId());
        if (!$project) {
            throw new tasksException('Project not found', 404);
        }

        if (!tsks()->getEntityRepository(tasksProject::class)->delete($project)) {
            throw new tasksException('Error on project delete');
        }

        return true;
    }
}
