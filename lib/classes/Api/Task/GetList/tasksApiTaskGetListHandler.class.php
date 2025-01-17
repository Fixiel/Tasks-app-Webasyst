<?php

final class tasksApiTaskGetListHandler
{
    /**
     * @param tasksApiTaskGetListRequest $filter
     *
     * @return tasksApiTasksResponse
     */
    public function getTasks(tasksApiTaskGetListRequest $filter): tasksApiTasksResponse
    {
        $collection = new tasksCollection($filter->getHash());
        $collectionInfo = $collection->getInfo();

        $order = $this->getOrder($collection, $filter->getOrder());
        $this->applyFilters($collection, $filter->getFilters());
        $this->applySince($collection, $filter->getSince());
        $this->applyOrder($collection, $order);

        $totalCount = null;
        $taskRows = $collection->getTasks(
            tasksCollection::FIELDS_TO_GET,
            $filter->getOffset(),
            $filter->getLimit(),
            $totalCount
        );

        tasksHelper::workupTasksForView($taskRows);

        return new tasksApiTasksResponse($taskRows, (int) $totalCount);
    }

    private function getOrder(tasksCollection $collection, string $order): string
    {
        if ($order === '') {
            $order = $this->getSavedOrder($collection);
        }
        if ($order === '') {
            $order = $this->getDefaultOrder($collection);
        }

        $this->saveOrder($collection, $order);

        return $order;
    }

    private function applyFilters(tasksCollection $collection, $filters): void
    {
        if ($filters) {
            $collection->filter($filters);
        }

        $type = $collection->getType();
        if (!in_array($type, [
                tasksCollection::HASH_SEARCH,
                tasksCollection::HASH_OUTBOX,
                tasksCollection::HASH_STATUS,
                tasksCollection::HASH_ID,
            ], true)
            && (strpos($filters, 'status_id') === false)
        ) {
            $collection->addWhere('t.status_id >= 0');
        }
    }

    private function applySince(tasksCollection $collection, ?int $since): void
    {
        if ($since) {
            $collection->addWhere(sprintf("t.update_datetime > '%s'", date('Y-m-d H:i:s', $since)));
        }
    }

    private function applyOrder(tasksCollection $c, $order)
    {
        switch ($order) {
            case tasksCollection::ORDER_NEWEST:
                $c->orderBy('update_datetime', 'DESC');
                break;
            case tasksCollection::ORDER_OLDEST:
                $c->orderBy('create_datetime');
                break;
            case tasksCollection::ORDER_DUE:
                $c->orderByDue();
                break;
            case tasksCollection::ORDER_PRIORITY:
            default:
                break; // Nothing to do: collection defaults to priority ordering
        }
    }

    private function saveOrder(tasksCollection $collection, $order): void
    {
        $key = $this->getOrderKey($collection);
        $order = is_scalar($order) ? (string) $order : '';
        $csm = new waContactSettingsModel();
        if ($order === '' || $order === $this->getDefaultOrder($collection)) {
            $csm->delete(wa()->getUser()->getId(), tasksConfig::APP_ID, $key);
        } else {
            $csm->set(wa()->getUser()->getId(), tasksConfig::APP_ID, $key, $order);
        }
    }

    private function getSavedOrder(tasksCollection $collection): string
    {
        $key = $this->getOrderKey($collection);
        $csm = new waContactSettingsModel();
        $order = $csm->getOne(wa()->getUser()->getId(), tasksConfig::APP_ID, $key);

        return is_scalar($order) ? (string) $order : '';
    }

    private function getDefaultOrder(tasksCollection $collection): string
    {
        $type = $collection->getType();
        $info = $collection->getInfo();
        if ($type === 'outbox') {
            $order = 'oldest';
        } elseif ($type === 'status' && $info['id'] == -1) {
            $order = 'newest';
        } else {
            $order = 'priority';
        }

        return $order;
    }

    private function getOrderKey(tasksCollection $collection): string
    {
        $type = $collection->getType();
        if ($type !== tasksCollection::HASH_STATUS) {
            return "tasks/tasks_order/{$type}";
        }

        $info = $collection->getInfo();

        return "tasks/tasks_order/{$type}/{$info['id']}";
    }
}
