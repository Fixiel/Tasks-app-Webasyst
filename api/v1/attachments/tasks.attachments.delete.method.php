<?php

class tasksAttachmentsDeleteMethod extends tasksApiAbstractMethod
{
    protected $method = [self::METHOD_POST, self::METHOD_DELETE];

    /**
     * @return tasksApiResponseInterface
     * @throws tasksApiMissingParamException
     * @throws tasksApiWrongParamException
     * @throws waException
     */
    public function run(): tasksApiResponseInterface
    {
        $request = new tasksApiAttachmentDeleteRequest($this->post('id', true, self::CAST_INT));

        if ((new tasksApiAttachmentDeleteHandler())->delete($request)) {
            return new tasksApiResponse(tasksApiResponse::HTTP_OK);
        }

        return new tasksApiResponse(tasksApiResponse::HTTP_OK, 'fail');
    }
}
